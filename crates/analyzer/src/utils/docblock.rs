use mago_allocator::Arena;
use std::rc::Rc;

use mago_codex::metadata::CodebaseMetadata;
use mago_codex::ttype::TType;
use mago_codex::ttype::TypeRef;
use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::atomic::object::TObject;
use mago_codex::ttype::atomic::object::r#enum::TEnum;
use mago_codex::ttype::atomic::object::named::TNamedObject;
use mago_codex::ttype::atomic::reference::TReference;
use mago_codex::ttype::atomic::scalar::TScalar;
use mago_codex::ttype::builder::get_union_from_type;
use mago_codex::ttype::builder::reject_unresolvable_tag_variable;
use mago_codex::ttype::comparator::ComparisonResult;
use mago_codex::ttype::comparator::union_comparator::can_expression_types_be_identical;
use mago_codex::ttype::comparator::union_comparator::is_contained_by;
use mago_codex::ttype::expander;
use mago_codex::ttype::expander::TypeExpansionOptions;
use mago_codex::ttype::get_mixed;
use mago_codex::ttype::union::TUnion;
use mago_codex::ttype::union::populate_union_type;
use mago_phpdoc_syntax::cst::Element;
use mago_phpdoc_syntax::cst::TagValue;
use mago_phpdoc_syntax::cst::r#type::Type as DocblockType;
use mago_reporting::Annotation;
use mago_reporting::Issue;
use mago_span::HasSpan;
use mago_span::Span;
use mago_syntax::comments::docblock::PrecedingDocblocks;
use mago_syntax::cst::Call;
use mago_syntax::cst::Expression;
use mago_syntax::cst::Variable;
use mago_word::Word;
use mago_word::word;

use crate::artifacts::AnalysisArtifacts;
use crate::code::IssueCode;
use crate::context::Context;
use crate::context::block::BlockContext;
use crate::utils::casing::check_class_like_casing;
use crate::utils::missing_type_hints::report_imprecise_type;
use crate::utils::missing_type_hints::report_missing_template_arguments;
use crate::utils::native_type::get_native_expression_type;
use mago_bytes::BytesDisplay;

/// Populates the context with variable types defined in the docblock.
///
/// This function retrieves all `@var`, `@psalm-var`, and `@phpstan-var` tags from the docblock
/// of the current statement in the context, parses their variable types, and inserts them
/// into the current block context.
///
/// # Arguments
///
/// * `context`: The main analysis context, providing access to the docblock parser and error collector.
/// * `block_context`: The current block context, which holds local variables and their types.
/// * `artifacts`: The analysis artifacts, which may be used to store or retrieve additional information.
/// * `override_existing`: A boolean indicating whether to override existing variable types in the block context.
pub fn populate_docblock_variables<'ctx, A>(
    context: &mut Context<'ctx, '_, A>,
    block_context: &mut BlockContext<'ctx>,
    artifacts: &mut AnalysisArtifacts,
    override_existing: bool,
) where
    A: Arena,
{
    populate_docblock_variables_excluding(context, block_context, artifacts, override_existing, None);
}

/// Reports a bare `array`, `list` or `iterable` anywhere in an inline `@var` type, like PHPStan's
/// missingType.iterableValue for `@var` tags.
fn report_imprecise_var_tag_types<A>(context: &mut Context<'_, '_, A>)
where
    A: Arena,
{
    if !context.settings.check_missing_type_hints {
        return;
    }

    let mut imprecise_types = Vec::new();
    for element in context.get_parsed_docblocks() {
        let Element::Tag(tag) = element else {
            continue;
        };

        let TagValue::Var(var_tag) = &tag.value else {
            continue;
        };

        let location = match &var_tag.variable {
            Some(variable) => format!("`@var` tag for variable `{}`", BytesDisplay(variable.value)),
            None => "`@var` tag".to_string(),
        };

        let mut found = Vec::new();
        collect_bare_iterable_types(var_tag.r#type, &mut found);
        imprecise_types.extend(found.into_iter().map(|(type_name, span)| (type_name, span, location.clone())));
    }

    for (type_name, span, location) in imprecise_types {
        report_imprecise_type(context, type_name, span, &location);
    }
}

fn collect_bare_iterable_types(r#type: &DocblockType<'_>, found: &mut Vec<(&'static str, Span)>) {
    let (type_name, parameters, span) = match r#type {
        DocblockType::Array(array) => ("array", array.parameters.as_ref(), array.span()),
        DocblockType::NonEmptyArray(array) => ("array", array.parameters.as_ref(), array.span()),
        DocblockType::AssociativeArray(array) => ("array", array.parameters.as_ref(), array.span()),
        DocblockType::List(list) => ("list", list.parameters.as_ref(), list.span()),
        DocblockType::NonEmptyList(list) => ("list", list.parameters.as_ref(), list.span()),
        DocblockType::Iterable(iterable) => ("iterable", iterable.parameters.as_ref(), iterable.span()),
        DocblockType::Reference(reference) => {
            if let Some(parameters) = &reference.parameters {
                for entry in parameters.entries.iter() {
                    collect_bare_iterable_types(&entry.inner, found);
                }
            }

            return;
        }
        DocblockType::Parenthesized(parenthesized) => return collect_bare_iterable_types(parenthesized.inner, found),
        DocblockType::Nullable(nullable) => return collect_bare_iterable_types(nullable.inner, found),
        DocblockType::TrailingPipe(trailing) => return collect_bare_iterable_types(trailing.inner, found),
        DocblockType::Slice(slice) => return collect_bare_iterable_types(slice.inner, found),
        DocblockType::Union(union) => {
            collect_bare_iterable_types(union.left, found);
            return collect_bare_iterable_types(union.right, found);
        }
        DocblockType::Intersection(intersection) => {
            collect_bare_iterable_types(intersection.left, found);
            return collect_bare_iterable_types(intersection.right, found);
        }
        DocblockType::Shape(shape) => {
            for field in shape.fields.iter() {
                collect_bare_iterable_types(field.value, found);
            }

            return;
        }
        _ => return,
    };

    match parameters {
        Some(parameters) => {
            for entry in parameters.entries.iter() {
                collect_bare_iterable_types(&entry.inner, found);
            }
        }
        None => found.push((type_name, span)),
    }
}

/// Same as `populate_docblock_variables`, but allows excluding a specific variable.
///
/// This is useful for assignment statements where we want to populate all @var annotations
/// except the one for the assignment target (which is handled by the assignment analyzer).
pub fn populate_docblock_variables_excluding<'ctx, A>(
    context: &mut Context<'ctx, '_, A>,
    block_context: &mut BlockContext<'ctx>,
    artifacts: &mut AnalysisArtifacts,
    override_existing: bool,
    exclude_variable: Option<Word>,
) where
    A: Arena,
{
    if PrecedingDocblocks::new(context.comments, context.statement_span.start.offset).next().is_none() {
        return;
    }

    report_imprecise_var_tag_types(context);

    // `self`, `static` and `$this` resolve to the enclosing class's lowercase name, which is no casing mistake.
    let enclosing_class = block_context.scope.get_class_like_name();

    for (name, variable_type, variable_type_span) in get_docblock_variables(context, block_context, artifacts, true) {
        if context.settings.check_missing_type_hints {
            let subject = match name {
                Some(variable_name) => format!("`@var` tag for variable `{variable_name}`"),
                None => "`@var` tag".to_string(),
            };

            report_missing_template_arguments(context, &variable_type, variable_type_span, &subject);
        }

        // Check for undefined type references in ALL @var types, regardless of variable name.
        for type_ref in variable_type.get_all_child_nodes() {
            if let TypeRef::Atomic(TAtomic::Object(
                TObject::Named(TNamedObject { name, .. }) | TObject::Enum(TEnum { name, .. }),
            )) = type_ref
                && Some(*name) != enclosing_class
            {
                check_class_like_casing(context, *name, variable_type_span);
            }

            let TypeRef::Atomic(TAtomic::Reference(TReference::Symbol { name: ref_name, .. })) = type_ref else {
                continue;
            };

            context.collector.report_with_code(
                IssueCode::NonExistentClassLike,
                Issue::error(format!("Cannot find class, interface, enum, or type alias `{ref_name}`."))
                    .with_annotation(
                        Annotation::primary(variable_type_span)
                            .with_message(format!("`{ref_name}` is not defined in the current codebase")),
                    )
                    .with_note("This error occurs when a type is referenced but not found in any analyzed source files or stubs.")
                    .with_note("If this type comes from an optional dependency or extension, you can safely suppress this issue using `@mago-ignore` or `@mago-expect`.")
                    .with_help("Verify the type name is spelled correctly, the file containing it is included in analysis, and any required `use` statements are present."),
            );
        }

        let Some(variable_name) = name else {
            continue;
        };

        // Skip if this variable should be excluded (handled by assignment analyzer)
        if exclude_variable.is_some_and(|excluded| variable_name == excluded) {
            continue;
        }

        insert_variable_from_docblock(
            context,
            block_context,
            variable_name,
            variable_type,
            variable_type_span,
            override_existing,
        );
    }
}

/// Retrieves all `@var`, `@psalm-var`, and `@phpstan-var` tags from the docblocks preceding the
/// current statement in the context, parsing their variable types.
///
/// This function scans the docblocks associated with the current statement in the context,
/// extracting all variable type declarations. It returns a vector of tuples, each containing:
///
/// - An optional variable name (if specified in the tag)
/// - The parsed type as a `TUnion`
/// - The span of the tag in the source code.
///
/// # Arguments
///
/// * `context`: The main analysis context, providing access to the docblock parser and error collector.
/// * `block_context`: The current block context, which may influence the parsing of docblocks.
/// * `artifacts`: The analysis artifacts, which may be used to store or retrieve additional information.
///
/// # Returns
///
/// A vector of tuples, where each tuple contains:
///
/// - `Option<String>`: The variable name if specified, or `None` if the tag is unnamed.
/// - `TUnion`: The parsed type from the tag.
/// - `Span`: The span of the tag in the source code.
pub fn get_docblock_variables<'ctx, A>(
    context: &mut Context<'ctx, '_, A>,
    block_context: &BlockContext<'ctx>,
    artifacts: &mut AnalysisArtifacts,
    allow_tracing: bool,
) -> Vec<(Option<mago_word::Word>, TUnion, Span)>
where
    A: Arena,
{
    context.get_parsed_docblocks()
        .into_iter()
        // Filter out non-tag elements
        .filter_map(|element| match element {
            Element::Tag(tag) => Some(tag),
            _ => None,
        })
        .filter_map(|tag| {
            if allow_tracing && let TagValue::Trace(trace) = &tag.value {
                let variable_name_bytes = trace.variable.value;
                let variable_atom = mago_word::word(variable_name_bytes);
                let variable_name = BytesDisplay(variable_name_bytes);
                match block_context.locals.get(&variable_atom) {
                    Some(variable_type) => {
                        let variable_type_str = variable_type.get_id();


                        context.collector.report_with_code(
                            IssueCode::PsalmTrace,
                            Issue::note(format!(
                                "Trace: Type of `{variable_name}` is `{variable_type_str}`"
                            ))
                            .with_annotation(
                                Annotation::primary(trace.variable.span)
                                    .with_message(format!("Type is: `{variable_type_str}`")),
                            )
                            .with_note(
                                "Spotted a `@psalm-trace` tag! While this works for compatibility, Mago has a more powerful way to inspect types.",
                            )
                            .with_help(
                                "For more flexible debugging, try using `Mago\\inspect()` directly in your code. It can inspect any expression, not just variables (e.g., `Mago\\inspect($foo->bar());`)."
                            ),
                        );
                    }
                    None => {
                        context.collector.report_with_code(
                            IssueCode::InvalidDocblock,
                            Issue::error(format!(
                                "Invalid `@psalm-trace`: Variable `{variable_name}` not found in this scope."
                            ))
                            .with_annotation(Annotation::primary(trace.variable.span).with_message(
                                "This variable is not defined or is out of scope here",
                            ))
                            .with_help(
                                "Check for typos or ensure the variable is defined on a path that reaches this docblock.",
                            ),
                        );
                    }
                }

                return None;
            }

            let TagValue::Var(var_tag) = &tag.value else {
                return None;
            };

            let variable_name = var_tag.variable.as_ref().map(|v| mago_word::word(v.value));

            // An inline `@var` has no parameter scope of its own, but it may copy the
            // type of an enclosing function parameter (`/** @var $x */ $y = ...`).
            // Anything else (`@var $label Label`) is a typo.
            let is_parameter_link =
                |name: &[u8]| block_context.scope.get_function_like().is_some_and(|f| f.get_parameter(word(name)).is_some());

            match reject_unresolvable_tag_variable(var_tag.r#type, is_parameter_link).and_then(|()| {
                get_union_from_type(
                    var_tag.r#type,
                    &context.scope,
                    &context.type_resolution_context,
                    block_context.scope.get_class_like_name(),
                )
            }) {
                Ok(mut variable_type) => {
                    populate_union_type(
                        &mut variable_type,
                        &context.codebase.symbols,
                        block_context.scope.get_reference_source().as_ref(),
                        &mut artifacts.symbol_references,
                        true,
                    );

                    expander::expand_union(
                        context.codebase,
                        &mut variable_type,
                        &TypeExpansionOptions {
                            self_class: block_context.scope.get_class_like_name(),
                            ..Default::default()
                        },
                    );

                    Some((variable_name, variable_type, var_tag.r#type.span()))
                }
                Err(type_error) => {
                    context.collector.report_with_code(
                        IssueCode::InvalidDocblock,
                        Issue::error(match variable_name {
                            Some(w) => format!("Invalid type in `@var` tag for variable `{w}`."),
                            None => "Invalid type in `@var` tag for variable `expression`.".to_string(),
                        })
                        .with_annotation(Annotation::primary(type_error.span()).with_message(type_error.to_string()))
                        .with_note(type_error.note())
                        .with_help(type_error.help()),
                    );

                    None
                }
            }
        })
        .collect::<Vec<_>>()
}

/// Finds the last applicable `@var` tag for a given variable and parses its type string.
///
/// This function retrieves the docblock associated with the current statement from the
/// context. It then iterates through all `@var`, `@psalm-var`, and `@phpstan-var` tags
/// to find the last one that applies to the specified `variable_id`. If a matching
/// tag is found, it attempts to parse the type string into a `TUnion`.
///
/// If parsing fails, a detailed error is reported to the user.
///
/// # Arguments
///
/// * `context`: The main analysis context, providing access to the docblock parser and error collector.
/// * `variable_id`: The name of the variable (e.g., "$foo") for which to find a type hint.
/// * `variable_span`: The span of the variable's usage, used for error reporting context.
///
/// # Returns
///
/// An `Option<TUnion>` containing the parsed type if a valid, matching `@var` tag
/// was found and successfully parsed. Returns `None` otherwise.
pub fn get_type_from_var_docblock<'ctx, A>(
    context: &mut Context<'ctx, '_, A>,
    block_context: &BlockContext<'ctx>,
    artifacts: &mut AnalysisArtifacts,
    value_expression_variable_id: Option<&[u8]>,
    mut allow_unnamed: bool,
) -> Option<(TUnion, Span)>
where
    A: Arena,
{
    allow_unnamed =
        allow_unnamed && !block_context.flags.inside_return() && !block_context.flags.inside_loop_expressions();

    get_docblock_variables(context, block_context, artifacts, false)
        .into_iter()
        .rfind(|(var_name, _, _)| match var_name {
            None if allow_unnamed => true,
            Some(name) if Some(name.as_bytes()) == value_expression_variable_id => true,
            _ => false,
        })
        .map(|(_, variable_type, variable_type_span)| (variable_type, variable_type_span))
}

/// Inserts a variable type from a docblock into the current block context.
///
/// This function is used to handle `@var` tags in docblocks, allowing the
/// type of a variable to be defined or overridden based on the docblock's
/// annotations. It checks if the variable already exists in the block context,
/// and if so, it verifies that the new type is compatible with the existing type.
///
/// # Arguments
///
/// * `context`: The main analysis context, providing access to the error collector.
/// * `block_context`: The current block context, which holds local variables and their types.
/// * `variable_name`: The name of the variable as specified in the docblock.
/// * `variable_type`: The type of the variable as a `TUnion`, parsed from the docblock.
/// * `variable_type_span`: The span of the variable type in the source code, used for error reporting.
/// * `override_existing`: A boolean indicating whether to override an existing variable type
pub fn insert_variable_from_docblock<'ctx, A>(
    context: &mut Context<'ctx, '_, A>,
    block_context: &mut BlockContext<'ctx>,
    variable_name: mago_word::Word,
    variable_type: TUnion,
    variable_type_span: Span,
    override_existing: bool,
) where
    A: Arena,
{
    if !override_existing && block_context.locals.contains_key(&variable_name) {
        return;
    }

    if let Some(previous_type) = block_context.locals.remove(&variable_name) {
        let is_super = is_contained_by(
            context.codebase,
            &variable_type,
            &previous_type,
            false,
            false,
            false,
            &mut ComparisonResult::default(),
        );

        let is_sub = is_contained_by(
            context.codebase,
            &previous_type,
            &variable_type,
            false,
            false,
            false,
            &mut ComparisonResult::default(),
        );

        let is_redundant = is_super
            && is_sub
            && !variable_type.is_mixed()
            && !previous_type.is_mixed()
            && !variable_type.is_generic_parameter()
            && !previous_type.is_generic_parameter()
            && !previous_type.contains_placeholder();

        let could_share_object_runtime_instance =
            unions_could_share_object_runtime_instance(&previous_type, &variable_type);

        let is_impossible = !is_redundant
            && !is_super
            && !is_sub
            && !could_share_object_runtime_instance
            && !can_expression_types_be_identical(context.codebase, &previous_type, &variable_type, false, false);

        if is_impossible {
            let variable_type_str = variable_type.get_id();
            let previous_type_str = previous_type.get_id();

            context.collector.report_with_code(
                IssueCode::DocblockTypeMismatch,
                Issue::error(format!("Docblock type mismatch for variable `{variable_name}`."))
                    .with_annotation(
                        Annotation::primary(variable_type_span)
                            .with_message(format!("This docblock asserts the type should be `{variable_type_str}`, but it was previously defined as `{previous_type_str}`.")),
                    )
                    .with_note("The type of the variable defined in the docblock does not match the previously defined type.")
                    .with_help(format!(
                        "Change the docblock type to match `{previous_type_str}`, or update the variable definition to a compatible type `{variable_type_str}`."
                    )),
            );
        } else if is_redundant {
            context.collector.report_with_code(
                IssueCode::RedundantDocblockType,
                Issue::warning(format!("Redundant docblock type for variable `{variable_name}`."))
                    .with_annotation(Annotation::primary(variable_type_span).with_message(format!(
                        "This docblock asserts the type should be `{}`, which is identical to the previously defined type.",
                        variable_type.get_id(),
                    )))
                    .with_help("You can remove this redundant `@var` docblock tag."),
            );
        }

        block_context.locals.insert(variable_name, Rc::new(variable_type));
    } else if block_context.scope.get_function_like().is_none() || variable_name.as_bytes() == b"$this" {
        block_context.locals.insert(variable_name, Rc::new(variable_type));
    }
}

pub fn check_docblock_type_against_native_type<A>(
    context: &mut Context<'_, '_, A>,
    block_context: &BlockContext<'_>,
    artifacts: &AnalysisArtifacts,
    variable_id: &[u8],
    assigned_type: &TUnion,
    docblock_type: &TUnion,
    docblock_type_span: Span,
    source_expression: &Expression<'_>,
) -> bool
where
    A: Arena,
{
    let codebase = context.codebase;
    let (native_type, is_valid) = match source_expression.unparenthesized() {
        Expression::Literal(_) | Expression::ConstantAccess(_) | Expression::Array(_) | Expression::LegacyArray(_) => {
            (assigned_type.clone(), types_overlap(codebase, assigned_type, docblock_type))
        }
        expression => {
            let Some(native_type) = get_native_expression_type(context, block_context, artifacts, expression) else {
                return false;
            };

            let is_subtype = is_contained_by(
                codebase,
                docblock_type,
                &native_type,
                false,
                false,
                false,
                &mut ComparisonResult::default(),
            );

            (native_type, is_subtype)
        }
    };

    if is_valid {
        return false;
    }

    let variable_id = BytesDisplay(variable_id);
    let docblock_type_str = docblock_type.get_id();
    let native_type_str = native_type.get_id();

    context.collector.report_with_code(
        IssueCode::DocblockTypeMismatch,
        Issue::error(format!("Docblock type mismatch for variable `{variable_id}`."))
            .with_annotation(Annotation::primary(docblock_type_span).with_message(format!(
                "This docblock asserts the type should be `{docblock_type_str}`, which is not a subtype of the native type `{native_type_str}`."
            )))
            .with_annotation(
                Annotation::secondary(source_expression.span())
                    .with_message(format!("This expression has the native type `{native_type_str}`.")),
            )
            .with_note("A `@var` tag may narrow the type an expression has without docblocks, never widen or contradict it.")
            .with_help(format!("Change the `@var` tag to a subtype of `{native_type_str}`.")),
    );

    true
}

fn types_overlap(codebase: &CodebaseMetadata, left: &TUnion, right: &TUnion) -> bool {
    is_contained_by(codebase, left, right, false, false, false, &mut ComparisonResult::default())
        || is_contained_by(codebase, right, left, false, false, false, &mut ComparisonResult::default())
        || unions_could_share_object_runtime_instance(left, right)
        || can_expression_types_be_identical(codebase, left, right, false, false)
}

pub fn check_docblock_type_incompatibility<A>(
    context: &mut Context<'_, '_, A>,
    value_expression_variable_id: Option<&[u8]>,
    value_expression_span: Span,
    inferred_type: &TUnion,
    docblock_type: &TUnion,
    dockblock_type_span: Span,
    source_expression: Option<&Expression>,
    is_foreach_value: bool,
) where
    A: Arena,
{
    let is_super = is_contained_by(
        context.codebase,
        docblock_type,
        inferred_type,
        false,
        false,
        false,
        &mut ComparisonResult::default(),
    );

    let is_sub = is_contained_by(
        context.codebase,
        inferred_type,
        docblock_type,
        false,
        false,
        false,
        &mut ComparisonResult::default(),
    );

    let is_redundant = is_super
        && is_sub
        && !docblock_type.is_mixed()
        && !inferred_type.is_mixed()
        && !docblock_type.is_generic_parameter()
        && !inferred_type.is_generic_parameter()
        && !inferred_type.contains_placeholder();

    let is_impossible = !is_redundant
        && !is_super
        && !is_sub
        && !unions_could_share_object_runtime_instance(inferred_type, docblock_type)
        && !can_expression_types_be_identical(context.codebase, inferred_type, docblock_type, false, true);

    if is_impossible {
        let docblock_type_str = docblock_type.get_id();
        let inferred_type_str = inferred_type.get_id();

        let mut issue = if let Some(value_expression_variable_id) = value_expression_variable_id {
            let value_expression_variable_id = BytesDisplay(value_expression_variable_id);
            Issue::error(format!("Docblock type mismatch for variable `{value_expression_variable_id}`."))
                .with_annotation(
                    Annotation::primary(dockblock_type_span)
                        .with_message(format!("This docblock asserts the type should be `{docblock_type_str}`...")),
                )
        } else {
            Issue::error("Docblock type mismatch for expression.").with_annotation(
                Annotation::primary(dockblock_type_span)
                    .with_message(format!("This docblock asserts the type should be `{docblock_type_str}`...")),
            )
        };

        if let Some(value_expression_variable_id) = value_expression_variable_id {
            let value_expression_variable_id = BytesDisplay(value_expression_variable_id);
            if let Some(source_expression) = source_expression {
                issue = issue.with_annotation(Annotation::secondary(source_expression.span()).with_message(format!(
                    "...but this expression provides an incompatible type `{inferred_type_str}`."
                )));
            }

            issue = issue.with_annotation(
                Annotation::secondary(value_expression_span)
                    .with_message(format!("The assignment to `{value_expression_variable_id}` here is invalid.")),
            ) .with_note(
                "The type of the assigned value and the `@var` docblock type have no overlap, making this assignment impossible."
            )
            .with_help(format!(
                "Change the assigned value to match `{docblock_type_str}`, or update the `@var` tag to a compatible type."
            ));
        } else {
            issue = issue.with_annotation(
                Annotation::secondary(value_expression_span)
                    .with_message(format!("...but this expression provides an incompatible type `{inferred_type_str}`.")),
            )
            .with_note(
                "The type resolved from the docblock and the type of the expression have no overlap, making the docblock type invalid.",
            )
            .with_help(format!(
                "Change the expression to match `{docblock_type_str}`, or update the `@var` tag to a compatible type."
            ));
        }

        context.collector.report_with_code(IssueCode::DocblockTypeMismatch, issue);

        return;
    }

    let widened_inferred_type = match source_expression {
        Some(source_expression) => {
            get_inferred_type_widened_by_docblock(context.codebase, inferred_type, docblock_type, source_expression)
        }
        None if is_foreach_value => {
            get_inferred_type_widened(context.codebase, inferred_type, docblock_type, false, false)
        }
        None => None,
    };

    if let Some(inferred_type) = widened_inferred_type {
        let docblock_type_str = docblock_type.get_id();
        let inferred_type_str = inferred_type.get_id();

        context.collector.report_with_code(
            IssueCode::DocblockTypeMismatch,
            Issue::error(format!("Docblock type `{docblock_type_str}` is not a subtype of the inferred type `{inferred_type_str}`."))
                .with_annotation(Annotation::primary(dockblock_type_span).with_message(format!(
                    "This docblock widens the type to `{docblock_type_str}`..."
                )))
                .with_annotation(Annotation::secondary(value_expression_span).with_message(format!(
                    "...but the assigned expression is known to be `{inferred_type_str}`."
                )))
                .with_note("A `@var` tag may narrow the type of an expression, but widening it hides what the code is known to hold.")
                .with_help(format!("Change the `@var` tag to `{inferred_type_str}` or a subtype of it, or remove it.")),
        );

        return;
    }

    if is_redundant {
        let docblock_type_str = docblock_type.get_id();
        let inferred_type_str = inferred_type.get_id();

        let mut issue = if let Some(value_expression_variable_id) = value_expression_variable_id {
            let value_expression_variable_id = BytesDisplay(value_expression_variable_id);
            Issue::warning(format!("Redundant docblock type for variable `{value_expression_variable_id}`."))
                .with_annotation(Annotation::primary(dockblock_type_span).with_message(format!(
                    "This docblock asserts the type should be `{docblock_type_str}`, which is identical to the inferred type."
                )))
        } else {
            Issue::warning("Redundant docblock type for expression.").with_annotation(
                Annotation::primary(dockblock_type_span).with_message(format!(
                    "This docblock asserts the type should be `{docblock_type_str}`, which is identical to the inferred type."
                )),
            )
        };

        if let Some(value_expression_variable_id) = value_expression_variable_id {
            let value_expression_variable_id = BytesDisplay(value_expression_variable_id);
            issue = issue
                .with_annotation(Annotation::secondary(value_expression_span).with_message(format!(
                    "The variable `{value_expression_variable_id}` type is known to be `{inferred_type_str}` here."
                )))
                .with_note("The type defined in this docblock is identical to the inferred type of the variable.")
                .with_help("You can remove this redundant `@var` docblock tag.");
        } else {
            issue = issue
                .with_annotation(
                    Annotation::secondary(value_expression_span)
                        .with_message(format!("This expression's type is already inferred as `{inferred_type_str}`.")),
                )
                .with_note("The type defined in this docblock is identical to the inferred type of the expression.")
                .with_help("You can remove this redundant `@var` docblock tag.");
        }

        context.collector.report_with_code(IssueCode::RedundantDocblockType, issue);
    }
}

fn is_scalar_like(expression: &Expression<'_>) -> bool {
    matches!(
        expression.unparenthesized(),
        Expression::Literal(_)
            | Expression::CompositeString(_)
            | Expression::MagicConstant(_)
            | Expression::ConstantAccess(_)
            | Expression::Array(_)
            | Expression::LegacyArray(_)
    )
}

fn get_inferred_type_widened_by_docblock(
    codebase: &CodebaseMetadata,
    inferred_type: &TUnion,
    docblock_type: &TUnion,
    source_expression: &Expression<'_>,
) -> Option<TUnion> {
    if is_scalar_like(source_expression) {
        return None;
    }

    get_inferred_type_widened(
        codebase,
        inferred_type,
        docblock_type,
        is_method_call_on_this(source_expression),
        matches!(source_expression.unparenthesized(), Expression::Instantiation(_)),
    )
}

fn get_inferred_type_widened(
    codebase: &CodebaseMetadata,
    inferred_type: &TUnion,
    docblock_type: &TUnion,
    keeps_static: bool,
    is_instantiation: bool,
) -> Option<TUnion> {
    let inferred_type =
        merge_empty_string_into_non_empty_string(relax_inferred_type(inferred_type, keeps_static, is_instantiation));
    let is_narrowing =
        is_contained_by(codebase, docblock_type, &inferred_type, false, false, false, &mut ComparisonResult::default());

    (!is_narrowing).then_some(inferred_type)
}

fn relax_inferred_type(inferred_type: &TUnion, keeps_static: bool, is_instantiation: bool) -> TUnion {
    TUnion::from_vec(
        inferred_type
            .types
            .iter()
            .map(|atomic| match atomic {
                TAtomic::Object(TObject::Named(named)) => {
                    let mut named = named.clone();
                    if !keeps_static {
                        named = named.with_is_static(false);
                    }

                    for type_parameter in named.get_type_parameters_mut().into_iter().flatten() {
                        if is_instantiation {
                            *type_parameter = get_mixed();
                        } else {
                            type_parameter.widen_literals();
                        }

                        if type_parameter.is_mixed() {
                            type_parameter.set_from_unspecified_template(true);
                        }
                    }

                    TAtomic::Object(TObject::Named(named))
                }
                atomic => atomic.clone(),
            })
            .collect(),
    )
}

fn merge_empty_string_into_non_empty_string(inferred_type: TUnion) -> TUnion {
    let is_empty_string =
        |atomic: &TAtomic| matches!(atomic, TAtomic::Scalar(TScalar::String(string)) if string.is_empty());
    let is_plain_non_empty_string = |atomic: &TAtomic| {
        matches!(
            atomic,
            TAtomic::Scalar(TScalar::String(string))
                if string.is_general()
                    && string.is_non_empty
                    && !string.is_truthy
                    && !string.is_numeric
                    && !string.is_callable
        )
    };

    if !inferred_type.types.iter().any(is_empty_string) || !inferred_type.types.iter().any(is_plain_non_empty_string) {
        return inferred_type;
    }

    TUnion::from_vec(
        inferred_type
            .types
            .iter()
            .filter(|atomic| !is_empty_string(atomic))
            .map(|atomic| match atomic {
                TAtomic::Scalar(TScalar::String(string)) if is_plain_non_empty_string(atomic) => {
                    let mut string = *string;
                    string.is_non_empty = false;

                    TAtomic::Scalar(TScalar::String(string))
                }
                atomic => atomic.clone(),
            })
            .collect(),
    )
}

fn is_method_call_on_this(expression: &Expression<'_>) -> bool {
    let object = match expression.unparenthesized() {
        Expression::Call(Call::Method(call)) => call.object,
        Expression::Call(Call::NullSafeMethod(call)) => call.object,
        _ => return false,
    };

    matches!(object, Expression::Variable(Variable::Direct(variable)) if variable.name == b"$this")
}

fn unions_could_share_object_runtime_instance(inferred_type: &TUnion, docblock_type: &TUnion) -> bool {
    for inferred_atomic in inferred_type.types.iter() {
        let TAtomic::Object(TObject::Named(inferred_named)) = inferred_atomic else {
            continue;
        };

        for docblock_atomic in docblock_type.types.iter() {
            let TAtomic::Object(TObject::Named(docblock_named)) = docblock_atomic else {
                continue;
            };

            if inferred_named.name.as_bytes().eq_ignore_ascii_case(docblock_named.name.as_bytes()) {
                return true;
            }
        }
    }

    false
}
