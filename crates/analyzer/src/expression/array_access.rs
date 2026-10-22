use mago_allocator::Arena;
use std::rc::Rc;

use mago_codex::identifier::function_like::FunctionLikeIdentifier;
use mago_codex::identifier::method::MethodIdentifier;
use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::atomic::object::TObject;
use mago_codex::ttype::expander::StaticClassType;
use mago_codex::ttype::get_arraykey;
use mago_codex::ttype::get_mixed;
use mago_codex::ttype::template::TemplateResult;
use mago_codex::ttype::union::TUnion;
use mago_reporting::Issue;
use mago_span::HasSpan;
use mago_syntax::cst::ArrayAccess;
use mago_syntax::cst::Expression;
use mago_word::Word;
use mago_word::WordMap;
use mago_word::concat_word;
use mago_word::word;

use crate::analyzable::Analyzable;
use crate::artifacts::AnalysisArtifacts;
use crate::code::IssueCode;
use crate::context::Context;
use crate::context::block::BlockContext;
use crate::error::AnalysisError;
use crate::invocation::Invocation;
use crate::invocation::InvocationArgumentsSource;
use crate::invocation::InvocationTarget;
use crate::invocation::MethodInvocationKind;
use crate::invocation::MethodTargetContext;
use crate::invocation::return_type_fetcher::fetch_declared_invocation_return_type;
use crate::invocation::return_type_fetcher::fetch_invocation_provider_return_type;
use crate::invocation::template_result::populate_template_result_from_invocation;
use crate::utils::expression::array::get_array_target_type_given_index;
use crate::utils::expression::array::get_array_value_union;
use crate::utils::expression::expression_is_nullsafe;
use crate::utils::expression::get_block_expression_id;
use crate::utils::expression::get_index_id;
use crate::utils::expression::has_dynamic_index;

impl<'ast, 'arena> Analyzable<'ast, 'arena> for ArrayAccess<'arena> {
    fn analyze<'ctx, A>(
        &'ast self,
        context: &mut Context<'ctx, 'arena, A>,
        block_context: &mut BlockContext<'ctx>,
        artifacts: &mut AnalysisArtifacts,
    ) -> Result<(), AnalysisError>
    where
        A: Arena,
    {
        let extended_var_id = get_block_expression_id(self.array, context, block_context);
        let keyed_array_var_id = extended_var_id.and_then(|array_id| {
            get_index_id(
                self.index,
                block_context.scope.get_class_like_name(),
                context.resolved_names,
                Some(context.codebase),
            )
            .map(|index| concat_word!(array_id.as_bytes(), b"[", index.as_bytes(), b"]"))
        });

        let index_offset = self.index.span().start.offset;
        artifacts.reported_array_index_offsets.remove(&index_offset);

        let saved_narrowed_type = if matches!(self.index, Expression::UnaryPostfix(_)) {
            keyed_array_var_id.as_ref().and_then(|k| block_context.locals.get(k).cloned())
        } else {
            None
        };

        let was_inside_isset = block_context.flags.inside_isset();
        let was_inside_general_use = block_context.flags.inside_general_use();
        let was_inside_unset = block_context.flags.inside_unset();
        let was_inside_variable_reference = block_context.flags.inside_variable_reference();

        block_context.flags.set_inside_isset(false);
        block_context.flags.set_inside_general_use(true);
        block_context.flags.set_inside_unset(false);
        block_context.flags.set_inside_variable_reference(false);

        self.index.analyze(context, block_context, artifacts)?;

        block_context.flags.set_inside_isset(was_inside_isset);
        block_context.flags.set_inside_unset(was_inside_unset);
        block_context.flags.set_inside_general_use(was_inside_general_use);
        block_context.flags.set_inside_variable_reference(was_inside_variable_reference);

        let index_type =
            artifacts.get_rc_expression_type(&self.index).cloned().unwrap_or_else(|| Rc::new(get_arraykey()));

        let was_inside_general_use = block_context.flags.inside_general_use();
        block_context.flags.set_inside_general_use(true);
        self.array.analyze(context, block_context, artifacts)?;
        block_context.flags.set_inside_general_use(was_inside_general_use);

        if let (Some(array_id), Some(keyed_array_var_id)) = (extended_var_id, keyed_array_var_id) {
            remember_first_or_last_key_of_non_empty_array(
                context,
                block_context,
                artifacts,
                self,
                array_id,
                keyed_array_var_id,
            );
        }

        if let Some(keyed_array_var_id) = &keyed_array_var_id
            && block_context.has_variable_atom(*keyed_array_var_id)
            && let Some(array_access_type) = block_context.locals.get(keyed_array_var_id).cloned()
        {
            let is_variable_key = has_dynamic_index(keyed_array_var_id.as_bytes());
            if is_variable_key || !array_access_type.possibly_undefined() {
                let is_unnarrowed_read = artifacts
                    .read_memoized_array_accesses
                    .get(keyed_array_var_id)
                    .is_some_and(|read_type| Rc::ptr_eq(read_type, &array_access_type));

                report_stored_read_index_type(
                    context,
                    block_context,
                    artifacts,
                    self,
                    &index_type,
                    extended_var_id,
                    is_unnarrowed_read,
                );
                artifacts.set_rc_expression_type(self, Rc::clone(&array_access_type));

                return Ok(());
            }
        }

        if let Some(saved_type) = saved_narrowed_type {
            let is_variable_key = keyed_array_var_id.as_ref().is_some_and(|k| has_dynamic_index(k.as_bytes()));
            if is_variable_key || !saved_type.possibly_undefined() {
                report_stored_read_index_type(
                    context,
                    block_context,
                    artifacts,
                    self,
                    &index_type,
                    extended_var_id,
                    false,
                );
                artifacts.set_rc_expression_type(self, saved_type);

                return Ok(());
            }
        }

        let container_type = artifacts.get_rc_expression_type(&self.array).cloned();

        if let Some(container_type) = container_type {
            let (access_type, index_reported) = get_array_target_type_given_index(
                context,
                block_context,
                self.span(),
                self.array.span(),
                Some(self.index.span()),
                &container_type,
                &index_type,
                false,
                extended_var_id,
                None,
                container_type.can_be_null() && expression_is_nullsafe(self.array),
                true,
            );

            if index_reported {
                artifacts.reported_array_index_offsets.insert(index_offset);
            }

            let access_type = Rc::new(access_type);

            let access_type =
                match get_offset_get_read_type(context, block_context, artifacts, self, &container_type, &index_type) {
                    Some(offset_get_type) => Rc::new(offset_get_type),
                    None => access_type,
                };

            if let Some(keyed_array_var_id) = &keyed_array_var_id {
                let can_store_result = block_context.flags.inside_assignment() || !container_type.is_mixed();

                if !block_context.flags.inside_isset()
                    && can_store_result
                    && has_dynamic_index(keyed_array_var_id.as_bytes())
                    && !block_context.locals.contains_key(keyed_array_var_id)
                {
                    block_context.locals.insert(*keyed_array_var_id, Rc::clone(&access_type));
                    artifacts.read_memoized_array_accesses.insert(*keyed_array_var_id, Rc::clone(&access_type));
                }
            }

            artifacts.set_rc_expression_type(self, access_type);
        } else {
            artifacts.set_expression_type(self, get_mixed());
        }

        Ok(())
    }
}

fn remember_first_or_last_key_of_non_empty_array<'ctx, A>(
    context: &Context<'ctx, '_, A>,
    block_context: &mut BlockContext<'ctx>,
    artifacts: &AnalysisArtifacts,
    array_access: &ArrayAccess<'_>,
    array_id: Word,
    keyed_array_var_id: Word,
) where
    A: Arena,
{
    let key_of_same_array = [b"(array_key_first(".as_slice(), b"(array_key_last("].into_iter().any(|prefix| {
        keyed_array_var_id.as_bytes()
            == concat_word!(array_id.as_bytes(), b"[", prefix, array_id.as_bytes(), b"))]").as_bytes()
    });
    if !key_of_same_array || block_context.locals.contains_key(&keyed_array_var_id) {
        return;
    }

    let Some(array_type) = artifacts.get_expression_type(array_access.array) else {
        return;
    };

    if array_type.types.is_empty() || !array_type.types.iter().all(TAtomic::is_non_empty_array) {
        return;
    }

    if let Some(value_type) = get_array_value_union(array_type, context.codebase) {
        block_context.locals.insert(keyed_array_var_id, Rc::new(value_type));
    }
}

fn report_stored_read_index_type<'ctx, 'arena, A>(
    context: &mut Context<'ctx, 'arena, A>,
    block_context: &BlockContext<'ctx>,
    artifacts: &mut AnalysisArtifacts,
    array_access: &ArrayAccess<'arena>,
    index_type: &TUnion,
    extended_var_id: Option<Word>,
    is_unnarrowed_read: bool,
) where
    A: Arena,
{
    let Some(container_type) = artifacts.get_rc_expression_type(array_access.array).cloned() else {
        return;
    };

    let ((_, index_reported), recorded_issues) = context.record(|context| {
        get_array_target_type_given_index(
            context,
            block_context,
            array_access.span(),
            array_access.array.span(),
            Some(array_access.index.span()),
            &container_type,
            index_type,
            false,
            extended_var_id,
            None,
            container_type.can_be_null() && expression_is_nullsafe(array_access.array),
            true,
        )
    });

    context.collector.extend(recorded_issues.into_iter().filter(|issue| {
        is_index_type_issue(issue) || (is_unnarrowed_read && is_possibly_undefined_index_issue(issue))
    }));

    if index_reported {
        artifacts.reported_array_index_offsets.insert(array_access.index.span().start.offset);
    }
}

pub(crate) fn report_index_types_of_array_access<'ctx, 'arena, A>(
    context: &mut Context<'ctx, 'arena, A>,
    block_context: &mut BlockContext<'ctx>,
    artifacts: &mut AnalysisArtifacts,
    array_access: &ArrayAccess<'arena>,
) -> Result<(), AnalysisError>
where
    A: Arena,
{
    let (result, recorded_issues) = context.record(|context| array_access.analyze(context, block_context, artifacts));

    context.collector.extend(recorded_issues.into_iter().filter(is_index_type_issue));

    result
}

fn is_possibly_undefined_index_issue(issue: &Issue) -> bool {
    const POSSIBLY_UNDEFINED_INDEX_CODES: [IssueCode; 2] =
        [IssueCode::PossiblyUndefinedStringArrayIndex, IssueCode::PossiblyUndefinedIntArrayIndex];

    POSSIBLY_UNDEFINED_INDEX_CODES.iter().any(|code| issue.code.as_deref() == Some(code.as_str()))
}

fn is_index_type_issue(issue: &Issue) -> bool {
    const INDEX_TYPE_CODES: [IssueCode; 5] = [
        IssueCode::MixedArrayIndex,
        IssueCode::InvalidArrayIndex,
        IssueCode::MismatchedArrayIndex,
        IssueCode::NullArrayIndex,
        IssueCode::PossiblyNullArrayIndex,
    ];

    INDEX_TYPE_CODES.iter().any(|code| issue.code.as_deref() == Some(code.as_str()))
}

/// Types `$object[$offset]` the same as `$object->offsetGet($offset)` when a return-type
/// provider handles that call or the declared return type is conditional on the offset.
fn get_offset_get_read_type<'ctx, 'arena, A>(
    context: &mut Context<'ctx, 'arena, A>,
    block_context: &BlockContext<'ctx>,
    artifacts: &AnalysisArtifacts,
    array_access: &ArrayAccess<'arena>,
    container_type: &TUnion,
    index_type: &TUnion,
) -> Option<TUnion>
where
    A: Arena,
{
    let [TAtomic::Object(TObject::Named(named_object))] = container_type.types.as_ref() else {
        return None;
    };

    let codebase = context.codebase;
    if !codebase.is_instance_of(named_object.name.as_bytes(), b"ArrayAccess") {
        return None;
    }

    let class_like_metadata = codebase.get_class_like(named_object.name.as_bytes())?;
    let method_identifier =
        codebase.get_declaring_method_identifier(&MethodIdentifier::new(named_object.name, word(b"offsetGet")));
    let method_metadata = codebase.get_method_by_id(&method_identifier)?;

    let invocation = Invocation::new(
        InvocationTarget::FunctionLike {
            identifier: FunctionLikeIdentifier::Method(
                method_identifier.get_class_name(),
                method_identifier.get_method_name(),
            ),
            metadata: method_metadata,
            inferred_return_type: None,
            effective_signature: None,
            method_context: Some(MethodTargetContext {
                invocation_kind: MethodInvocationKind::Instance,
                declaring_method_id: Some(method_identifier),
                class_like_metadata,
                class_type: StaticClassType::Object(TObject::Named(named_object.clone())),
                declaring_object_type: None,
            }),
            span: array_access.span(),
        },
        InvocationArgumentsSource::ArrayAccessIndex(array_access),
        array_access.span(),
    );

    if let Some(provided_type) = fetch_invocation_provider_return_type(context, block_context, artifacts, &invocation) {
        return Some(provided_type);
    }

    let declared_return_type = method_metadata.return_type_metadata.as_ref()?;
    if !declared_return_type.type_union.types.iter().any(TAtomic::is_conditional) {
        return None;
    }

    let mut template_result = TemplateResult::default();
    populate_template_result_from_invocation(context, &invocation, &mut template_result);

    let mut parameters = WordMap::default();
    if let Some(offset_parameter) = method_metadata.parameters.first() {
        parameters.insert(offset_parameter.get_name().0, index_type.clone());
    }

    Some(fetch_declared_invocation_return_type(context, &invocation, &template_result, &parameters))
}

#[cfg(test)]
#[allow(clippy::unwrap_used)]
mod tests {
    use std::borrow::Cow;
    use std::sync::Arc;
    use std::sync::LazyLock;
    use std::sync::Mutex;

    use foldhash::HashSet;
    use indoc::indoc;
    use mago_allocator::LocalArena;
    use mago_codex::populator::populate_codebase;
    use mago_codex::scanner::scan_program;
    use mago_codex::ttype::get_int;
    use mago_codex::ttype::union::TUnion;
    use mago_database::DatabaseReader;
    use mago_database::file::File;
    use mago_names::resolver::NameResolver;
    use mago_prelude::Prelude;
    use mago_span::HasSpan;
    use mago_syntax::cst::Expression;
    use mago_syntax::parser::parse_file;
    use mago_word::WordSet;

    use crate::Analyzer;
    use crate::analysis_result::AnalysisResult;
    use crate::code::IssueCode;
    use crate::plugin::PluginRegistry;
    use crate::plugin::context::HookContext;
    use crate::plugin::context::InvocationInfo;
    use crate::plugin::context::ProviderContext;
    use crate::plugin::hook::ExpressionHook;
    use crate::plugin::hook::HookResult;
    use crate::plugin::provider::Provider;
    use crate::plugin::provider::ProviderMeta;
    use crate::plugin::provider::method::MethodReturnTypeProvider;
    use crate::plugin::provider::method::MethodTarget;
    use crate::settings::Settings;
    use crate::test_analysis;

    type IndexReads = Arc<Mutex<Vec<((u32, u32), bool, bool)>>>;

    struct IndexReadTypes {
        reads: IndexReads,
    }

    impl Provider for IndexReadTypes {
        fn meta() -> &'static ProviderMeta {
            static META: ProviderMeta =
                ProviderMeta::new("index-read-types", "Index read types", "Records array index read types");
            &META
        }
    }

    impl ExpressionHook for IndexReadTypes {
        fn after_expression(&self, expression: &Expression<'_>, context: &mut HookContext<'_, '_>) -> HookResult<()> {
            if let Expression::ArrayAccess(_) = expression
                && let Some(read) = context.get_expression_type(expression)
                && let Ok(mut reads) = self.reads.lock()
            {
                let span = expression.span();
                reads.push(((span.start.offset, span.end.offset), read.is_nullable(), read.has_error_path_null()));
            }

            Ok(())
        }
    }

    struct OffsetGetReturnsInt;

    impl Provider for OffsetGetReturnsInt {
        fn meta() -> &'static ProviderMeta {
            static META: ProviderMeta =
                ProviderMeta::new("offset-get-returns-int", "Bag::offsetGet", "Types every offsetGet() call as int");
            &META
        }
    }

    impl MethodReturnTypeProvider for OffsetGetReturnsInt {
        fn targets() -> &'static [MethodTarget] {
            static TARGETS: [MethodTarget; 1] = [MethodTarget::exact(b"bag", b"offsetget")];
            &TARGETS
        }

        fn get_return_type(
            &self,
            _context: &ProviderContext<'_, '_, '_>,
            _class_name: &[u8],
            _method_name: &[u8],
            _invocation: &InvocationInfo<'_, '_, '_>,
        ) -> Option<TUnion> {
            Some(get_int())
        }
    }

    static PRELUDE: LazyLock<Prelude> = LazyLock::new(Prelude::build);

    fn analyze(code: String, registry: &PluginRegistry, settings: Settings) -> AnalysisResult {
        let Prelude { mut database, mut metadata, mut symbol_references } = PRELUDE.clone();
        let file_id = database.add(File::ephemeral(Cow::Borrowed(b"read.php"), Cow::Owned(code.into_bytes())));
        let file = database.get_ref(&file_id).unwrap();
        let arena = LocalArena::new();
        let program = parse_file(&arena, file);
        assert!(!program.has_errors());
        let resolved_names = NameResolver::new(&arena).resolve(program);
        metadata.extend(scan_program(&arena, file, program, &resolved_names, settings.version));
        populate_codebase(&mut metadata, &mut symbol_references, WordSet::default(), HashSet::default());
        let mut result = AnalysisResult::new(symbol_references);
        Analyzer::new(&arena, file, &resolved_names, &metadata, registry, settings)
            .analyze(program, &mut result)
            .unwrap();

        result
    }

    #[test]
    fn array_access_read_takes_the_type_a_provider_gives_offset_get() {
        let code = "<?php /** @implements ArrayAccess<string, mixed> */ final class Bag implements ArrayAccess { public function offsetExists(mixed $offset): bool { return true; } public function offsetGet(mixed $offset): mixed { return null; } public function offsetSet(mixed $offset, mixed $value): void {} public function offsetUnset(mixed $offset): void {} } function read(Bag $bag): int { return $bag['id']; }";
        let mut registry = PluginRegistry::with_library_providers();
        let without_provider = analyze(code.to_string(), &registry, Settings::default());
        assert!(
            without_provider.issues.iter().any(|issue| issue.code.as_deref() == Some("mixed-return-statement")),
            "{:?}",
            without_provider.issues
        );

        registry.register_method_provider(OffsetGetReturnsInt);
        let with_provider = analyze(code.to_string(), &registry, Settings::default());
        assert!(with_provider.issues.is_empty(), "{:?}", with_provider.issues);
    }

    fn index_read_type(array_type: &str, read: &str) -> (bool, bool) {
        let code =
            format!("<?php /** @param {array_type} $a */ function probe(array $a, string $k): void {{ {read}; }}");
        let start = u32::try_from(code.rfind(read).unwrap()).unwrap();
        let end = start + u32::try_from(read.len()).unwrap();
        let reads = IndexReads::default();
        let mut registry = PluginRegistry::with_library_providers();
        registry.register_expression_hook(IndexReadTypes { reads: Arc::clone(&reads) });
        analyze(code, &registry, Settings { strict_array_index_existence: true, ..Settings::default() });

        let reads = reads.lock().unwrap();
        let (_, nullable, marked) = reads.iter().find(|(span, _, _)| *span == (start, end)).unwrap();

        (*nullable, *marked)
    }

    #[test]
    fn possibly_undefined_index_reads_mark_only_the_null_they_add() {
        assert_eq!(index_read_type("list<int>", "$a[1]"), (true, true));
        assert_eq!(index_read_type("array{k?: int}", "$a['k']"), (true, true));
        assert_eq!(index_read_type("array{k?: array{j: int}}", "$a['k']['j']"), (true, true));
        assert_eq!(index_read_type("array<string, int>", "$a['k']"), (true, true));
        assert_eq!(index_read_type("array{k: int, ...<string, int>}", "$a[$k]"), (true, true));
        assert_eq!(index_read_type("array{k: ?int}", "$a['k']"), (true, false));
    }

    test_analysis! {
        name = using_generic_parameter_as_index,
        code = indoc! {"
            <?php

            /**
             * @template T as string|lowercase-string
             *
             * @param array<T, bool> $old
             * @param array<T, bool> $new
             * @return array<T, bool>
             */
            function mergeThreadData(array $old, array $new): array {
                foreach ($new as $name => $value) {
                    if (!isset($old[$name]) || !$old[$name] && $value) {
                        $old[$name] = $value;
                    }
                }

                return $old;
            }
        "},
    }

    test_analysis! {
        name = negated_isset_narrows_parent_array_type,
        code = indoc! {"
            <?php

            /**
             * @return array{foo?: array{bar?: string}}
             */
            function y(): array { return []; }

            $y = y();

            if (isset($y['foo']) && !isset($y['foo']['bar'])) {
                echo $y['foo']['bar'];
                echo $y['foo']['baz'];
            }
        "},
        issues = [
            IssueCode::UndefinedStringArrayIndex,  // $y['foo']['bar']
            IssueCode::UndefinedStringArrayIndex,  // $y['foo']['baz']
        ],
    }

    test_analysis! {
        name = variable_key_narrowing_not_null,
        code = indoc! {"
            <?php

            /**
             * @param array<string, int|null> $map
             */
            function testNotNullVariableKeyNarrowing(array $map, string $key): int
            {
                if ($map[$key] !== null) {
                    return $map[$key];
                }

                return 0;
            }
        "},
    }
}
