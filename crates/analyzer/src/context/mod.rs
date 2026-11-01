use std::cell::RefCell;
use std::rc::Rc;

use foldhash::HashMap;
use foldhash::HashSet;
use mago_algebra::clause::Clause;
use mago_allocator::Arena;
use mago_word::Word;
use mago_word::WordMap;
use mago_word::WordSet;

use mago_codex::metadata::CodebaseMetadata;
use mago_codex::metadata::class_like::ClassLikeMetadata;
use mago_codex::reference::SymbolReferences;
use mago_codex::ttype::resolution::TypeResolutionContext;
use mago_codex::ttype::union::TUnion;
use mago_collector::Collector;
use mago_database::file::File;
use mago_names::ResolvedNames;
use mago_names::scope::NamespaceScope;
use mago_phpdoc_syntax::PHPDocParser;
use mago_phpdoc_syntax::cst::Element;
use mago_reporting::Annotation;
use mago_reporting::Issue;
use mago_reporting::IssueCollection;
use mago_span::HasSpan;
use mago_span::Span;
use mago_syntax::comments::docblock::PrecedingDocblocks;
use mago_syntax::cst::Expression;
use mago_syntax::cst::Identifier;
use mago_syntax::cst::Literal;
use mago_syntax::cst::LiteralInteger;
use mago_syntax::cst::Program;
use mago_syntax::cst::Statement;
use mago_syntax::cst::Trivia;

use crate::analysis_result::AnalysisResult;
use crate::artifacts::AnalysisArtifacts;
use crate::code::IssueCode;
use crate::context::assertion::AssertionContext;
use crate::context::block::BlockContext;
use crate::context::docblock_facts::DocblockFacts;
use crate::external::ExternalAnalysisSession;
use crate::plugin::PluginRegistry;
use crate::resolver::function::resolve_function_name;
use crate::settings::Settings;
use crate::utils::last_condition::collect_last_conditions;

type PropertyTypes = Vec<(Word, Rc<TUnion>)>;

pub mod assertion;
pub mod block;
pub mod block_flags;
pub mod conditional_expression;
pub mod docblock_facts;
pub mod scope;
pub mod utils;

#[derive(Debug)]
#[allow(clippy::field_scoped_visibility_modifiers)]
#[allow(clippy::struct_field_names)]
pub struct Context<'ctx, 'arena, A>
where
    A: Arena,
{
    pub(super) arena: &'arena A,
    pub(super) codebase: &'ctx CodebaseMetadata,
    pub(super) source_file: &'ctx File,
    pub(super) resolved_names: &'ctx ResolvedNames<'arena>,
    pub(super) type_resolution_context: TypeResolutionContext,
    pub(super) comments: &'arena [Trivia<'arena>],
    pub(super) settings: &'ctx Settings,
    pub(super) scope: NamespaceScope,
    pub(super) collector: Collector<'ctx, 'arena, A>,
    pub(super) statement_span: Span,
    pub(super) body_block_span: Option<Span>,
    pub(super) plugin_registry: &'ctx PluginRegistry,
    pub(super) external_analysis_session: Option<&'ctx ExternalAnalysisSession>,
    /// The registry and worker session the native pass asks for the by-reference out types of a call.
    native_signature_source: Option<(&'ctx PluginRegistry, &'ctx ExternalAnalysisSession)>,
    pub(super) additional_symbol_references: Option<&'ctx SymbolReferences>,
    pub(super) property_types: HashMap<(Word, bool), PropertyTypes>,
    /// Types come from native declarations only, like PHPStan's scope with promoted native types.
    pub(super) native_types_only: bool,
    /// Facts the pass on docblock types records and the native pass applies.
    pub(crate) docblock_facts: Option<&'ctx RefCell<DocblockFacts>>,
    pub(super) strict_types: bool,
    /// `if` conditions and `else` clauses the native pass found always false or unreachable; `None` when
    /// docblock types are certain.
    pub(crate) natively_false_conditions: Option<HashSet<Span>>,
    /// Conditions PHPStan's `LastConditionVisitor` marks as last, where it reports no always-true check.
    pub(crate) last_conditions: HashSet<Span>,
    class_initializers: WordMap<WordSet>,
    pub(crate) constructor_initialized_properties: WordMap<WordSet>,
    /// `$this->prop` types of readonly properties where the constructor ends, like PHPStan's remembered constructor scope.
    pub(crate) constructor_property_types: WordMap<Vec<(Word, Rc<TUnion>)>>,
}

impl<'ctx, 'arena, A> Context<'ctx, 'arena, A>
where
    A: Arena,
{
    pub fn new(
        arena: &'arena A,
        codebase: &'ctx CodebaseMetadata,
        source: &'ctx File,
        resolved_names: &'ctx ResolvedNames<'arena>,
        settings: &'ctx Settings,
        statement_span: Span,
        comments: &'arena [Trivia<'arena>],
        collector: Collector<'ctx, 'arena, A>,
        plugin_registry: &'ctx PluginRegistry,
        external_analysis_session: Option<&'ctx ExternalAnalysisSession>,
        additional_symbol_references: Option<&'ctx SymbolReferences>,
    ) -> Self {
        Self {
            arena,
            codebase,
            source_file: source,
            resolved_names,
            type_resolution_context: TypeResolutionContext::new(),
            comments,
            settings,
            scope: NamespaceScope::default(),
            statement_span,
            body_block_span: None,
            collector,
            plugin_registry,
            external_analysis_session,
            native_signature_source: None,
            additional_symbol_references,
            property_types: HashMap::default(),
            native_types_only: false,
            docblock_facts: None,
            strict_types: false,
            natively_false_conditions: None,
            last_conditions: HashSet::default(),
            class_initializers: WordMap::default(),
            constructor_initialized_properties: WordMap::default(),
            constructor_property_types: WordMap::default(),
        }
    }

    #[must_use]
    pub(crate) fn with_native_types_only(mut self) -> Self {
        self.native_types_only = true;
        self.natively_false_conditions = Some(HashSet::default());
        self
    }

    #[must_use]
    pub(crate) fn with_native_signature_source(
        mut self,
        plugin_registry: &'ctx PluginRegistry,
        external_analysis_session: Option<&'ctx ExternalAnalysisSession>,
    ) -> Self {
        self.native_signature_source = external_analysis_session.map(|session| (plugin_registry, session));
        self
    }

    /// The registry and worker session that answer callable-signature requests.
    pub(crate) fn callable_signature_source(&self) -> Option<(&'ctx PluginRegistry, &'ctx ExternalAnalysisSession)> {
        self.native_signature_source
            .or_else(|| self.external_analysis_session.map(|session| (self.plugin_registry, session)))
    }

    #[must_use]
    pub(crate) fn with_strict_types(mut self, program: &Program<'_>) -> Self {
        self.strict_types = program.statements.iter().any(|statement| {
            matches!(statement, Statement::Declare(declare) if declare.items.iter().any(|item| {
                item.name.value.eq_ignore_ascii_case(b"strict_types")
                    && matches!(item.value, Expression::Literal(Literal::Integer(LiteralInteger { value: Some(1), .. })))
            }))
        });
        self
    }

    #[must_use]
    pub(crate) fn with_last_conditions(mut self, program: &Program<'_>) -> Self {
        self.last_conditions = collect_last_conditions(program);
        self
    }

    #[must_use]
    pub(crate) fn with_docblock_facts(mut self, facts: &'ctx RefCell<DocblockFacts>) -> Self {
        self.docblock_facts = Some(facts);
        self
    }

    /// Whether the pass on docblock types found the call at `call_span` to return `never`.
    pub(crate) fn is_docblock_never_call(&self, call_span: Span) -> bool {
        self.native_types_only && self.docblock_facts.is_some_and(|facts| facts.borrow().is_never_call(&call_span))
    }

    /// What the context's two-variable clauses make the disjunctions of a condition's formula imply, see
    /// [`mago_algebra::resolve_disjunctions`]. The native pass takes what the pass on docblock types derived, as
    /// PHPStan's native scope takes the sure types of an `||`.
    pub(crate) fn resolve_disjunctions<'clause>(
        &self,
        condition_span: Span,
        formula: &[Clause],
        context_clauses: impl IntoIterator<Item = &'clause Clause>,
    ) -> Vec<Clause> {
        let Some(facts) = self.docblock_facts else {
            return mago_algebra::resolve_disjunctions(formula, context_clauses);
        };

        if self.native_types_only {
            return facts.borrow().disjunction_consequences(&condition_span).cloned().unwrap_or_default();
        }

        let consequences = mago_algebra::resolve_disjunctions(formula, context_clauses);
        facts.borrow_mut().record_disjunction_consequences(condition_span, &consequences);
        consequences
    }

    pub(crate) fn record_never_call(&self, call_span: Span) {
        if !self.native_types_only
            && let Some(facts) = self.docblock_facts
        {
            facts.borrow_mut().record_never_call(call_span);
        }
    }

    #[must_use]
    pub(crate) fn with_natively_false_conditions(mut self, conditions: HashSet<Span>) -> Self {
        self.natively_false_conditions = Some(conditions);
        self
    }

    /// PHPStan skips the scope of a branch whose condition is always false, judged on native types
    /// under `treatPhpDocTypesAsCertain: false`.
    pub(crate) fn skips_false_branch(&mut self, condition_span: Span, condition_type: Option<&TUnion>) -> bool {
        if !condition_type.is_some_and(TUnion::is_always_falsy) {
            return false;
        }

        match self.natively_false_conditions.as_mut() {
            Some(conditions) if self.native_types_only => {
                conditions.insert(condition_span);
                true
            }
            Some(conditions) => conditions.contains(&condition_span),
            None => true,
        }
    }

    /// PHPStan takes a condition whose type is always true as always true, judged on native types under
    /// `treatPhpDocTypesAsCertain: false`: an `if` merges no `else`, and a `while` it enters runs until its body leaves.
    pub(crate) fn is_always_true_condition(&mut self, condition_span: Span, condition_type: Option<&TUnion>) -> bool {
        if !condition_type.is_some_and(TUnion::is_always_truthy) {
            return false;
        }

        match self.natively_false_conditions.as_mut() {
            Some(conditions) if self.native_types_only => {
                conditions.insert(condition_span);
                true
            }
            Some(conditions) => conditions.contains(&condition_span),
            None => true,
        }
    }

    /// PHPStan analyses an `else` the docblock types make unreachable; under `treatPhpDocTypesAsCertain:
    /// false` Mago does so when the native types leave it reachable.
    pub(crate) fn analyzes_unreachable_else(&mut self, else_span: Span) -> bool {
        match self.natively_false_conditions.as_mut() {
            Some(clauses) if self.native_types_only => {
                clauses.insert(else_span);
                false
            }
            Some(clauses) => !clauses.contains(&else_span),
            None => false,
        }
    }

    pub(crate) fn prepare_class_initializers(
        &mut self,
        class_like: &ClassLikeMetadata,
    ) -> Result<(), crate::error::AnalysisError> {
        if self.external_analysis_session.is_none() || !self.plugin_registry.may_have_class_initializer_provider() {
            return Ok(());
        }

        let classes =
            std::iter::once(class_like.name).chain(class_like.all_parent_classes.iter().copied()).collect::<Vec<_>>();
        for class in classes {
            if self.class_initializers.contains_key(&class) {
                continue;
            }

            let Some(metadata) = self.codebase.get_class_like(class.as_bytes()) else {
                continue;
            };
            let initializers =
                self.plugin_registry.get_class_initializers(self.codebase, metadata, self.external_analysis_session);
            self.class_initializers.insert(class, initializers);
        }

        Ok(())
    }

    #[inline]
    pub(crate) fn is_class_initializer_for(&self, metadata: &ClassLikeMetadata, method: Word) -> bool {
        self.settings.is_class_initializer_for(metadata, method)
            || self.class_initializers.get(&metadata.name).is_some_and(|methods| methods.contains(&method))
    }

    pub(crate) fn applicable_class_initializers<'meta>(
        &'meta self,
        metadata: &'meta ClassLikeMetadata,
    ) -> impl Iterator<Item = Word> + 'meta {
        self.settings
            .applicable_class_initializers(metadata)
            .chain(self.class_initializers.get(&metadata.name).into_iter().flat_map(|methods| methods.iter().copied()))
    }

    /// Resolves the correct function name based on PHP's dynamic name resolution rules.
    ///
    /// This function determines the fully qualified name (FQN) of a function being called,
    /// accounting for PHP's nuanced resolution rules:
    ///
    /// - If the function is explicitly imported via `use`, it resolves to the imported name.
    /// - If the function name starts with a leading `\`, it is treated as a global function.
    /// - If no `\` is present:
    ///   1. The function name is checked in the current namespace.
    ///   2. If not found, it falls back to the global namespace.
    ///   3. If neither exists, it defaults to the current namespace's FQN.
    ///
    /// # Arguments
    ///
    /// - `identifier`: The identifier representing the function name in the source code.
    ///
    /// # Returns
    ///
    /// - A reference to the resolved function name as a string.
    ///
    /// # Note
    ///
    /// Function names in PHP are case-insensitive; they are stored and looked up in lowercase
    /// within the codebase metadata.
    pub fn resolve_function_name<'ast>(&self, identifier: &'ast Identifier<'arena>) -> &'arena [u8] {
        resolve_function_name(identifier, self.resolved_names, self.codebase)
    }

    pub fn get_assertion_context_from_block(
        &self,
        block_context: &BlockContext<'ctx>,
    ) -> AssertionContext<'ctx, 'arena, A> {
        self.get_assertion_context(block_context.scope.get_class_like_name())
    }

    #[inline]
    pub fn get_assertion_context(&self, this_class_name: Option<Word>) -> AssertionContext<'ctx, 'arena, A> {
        AssertionContext {
            arena: self.arena,
            resolved_names: self.resolved_names,
            codebase: self.codebase,
            this_class_name,
            trust_existence_checks: self.settings.trust_existence_checks,
            docblock_facts: self.docblock_facts,
            native_types_only: self.native_types_only,
        }
    }

    pub fn get_parsed_docblocks(&mut self) -> Vec<Element<'arena>> {
        let mut elements = vec![];
        for trivia in PrecedingDocblocks::new(self.comments, self.statement_span.start.offset) {
            let document = PHPDocParser::parse_without_trivia(self.arena, trivia.value, trivia.span);

            for error in document.errors {
                let error_span = error.span();

                let mut issue = Issue::error(error.to_string())
                    .with_annotation(
                        Annotation::primary(error_span).with_message("This part of the docblock has a syntax error"),
                    )
                    .with_note(error.note());

                if trivia.span != error_span {
                    issue = issue.with_annotation(
                        Annotation::secondary(trivia.span).with_message("The error is within this docblock"),
                    );
                }

                issue = issue.with_annotation(
                    Annotation::secondary(self.statement_span)
                        .with_message("This docblock is associated with the following statement"),
                );

                issue = issue.with_help(error.help());

                self.collector.report_with_code(IssueCode::InvalidDocblock, issue);
            }

            elements.extend(document.elements.iter().copied());
        }

        elements
    }

    pub fn record<T>(&mut self, callback: impl FnOnce(&mut Context<'ctx, 'arena, A>) -> T) -> (T, IssueCollection) {
        self.collector.start_recording();
        let result = callback(self);
        let issues = self.collector.finish_recording().unwrap_or_default();

        (result, issues)
    }

    pub fn finish(self, artifacts: AnalysisArtifacts, analysis_result: &mut AnalysisResult) {
        analysis_result.issues.extend(self.collector.finish());
        analysis_result.symbol_references.extend(artifacts.symbol_references);
    }

    /// Drain the collector into the analysis result and return any
    /// unreported issues. Used by [`crate::Analyzer::analyze_with_artifacts`]
    /// when the caller needs to retain ownership of [`AnalysisArtifacts`]
    /// after analysis completes.
    pub fn finish_collector(self, analysis_result: &mut AnalysisResult, defer_pragmas: bool) {
        if defer_pragmas {
            let (issues, pragmas) = self.collector.defer();
            analysis_result.issues.extend(issues);
            analysis_result.add_deferred_pragmas(pragmas);
        } else {
            analysis_result.issues.extend(self.collector.finish());
        }
    }
}
