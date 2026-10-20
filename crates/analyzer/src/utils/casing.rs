use mago_allocator::Arena;
use mago_codex::metadata::class_like::ClassLikeMetadata;
use mago_codex::metadata::function_like::FunctionLikeMetadata;
use mago_reporting::Annotation;
use mago_reporting::Issue;
use mago_span::HasSpan;
use mago_span::Span;
use mago_syntax::cst::ClassLikeMember;
use mago_syntax::cst::ClassLikeMemberSelector;
use mago_syntax::cst::Hint;
use mago_syntax::cst::Program;
use mago_syntax::cst::Use;
use mago_syntax::cst::UseItems;
use mago_syntax::cst::UseType;
use mago_syntax::walker::Walker;
use mago_word::Word;
use mago_word::concat_word;
use mago_word::word;

use crate::code::IssueCode;
use crate::context::Context;
use crate::resolver::method::ResolvedMethod;

/// Checks if the used class-like name matches the canonical casing and reports if not.
pub fn check_class_like_casing<A>(context: &mut Context<'_, '_, A>, used_name: Word, span: Span)
where
    A: Arena,
{
    if !context.settings.check_name_casing {
        return;
    }

    let Some(metadata) = context.codebase.get_class_like(used_name.as_bytes()) else {
        return;
    };

    let canonical = metadata.original_name;
    if canonical == used_name || !canonical.as_bytes().eq_ignore_ascii_case(used_name.as_bytes()) {
        return;
    }

    // Extract the mismatched part (could be namespace or class name)
    // Compare only the parts that differ
    context.collector.report_with_code(
        IssueCode::IncorrectClassLikeCasing,
        Issue::warning(format!("Incorrect casing for class-like `{used_name}`."))
            .with_annotation(
                Annotation::primary(span).with_message(format!("Used as `{used_name}`, but defined as `{canonical}`.")),
            )
            .with_note("Using incorrect casing can cause autoloading failures on case-sensitive file systems.")
            .with_help(format!("Use the correct casing: `{canonical}`.")),
    );
}

fn written_class_name(resolved: &[u8], written: &[u8]) -> Option<Word> {
    let written = mago_bytes::trim_start_byte(written, b'\\');
    let prefix_length = resolved.len().checked_sub(written.len())?;

    let (prefix, imported) = resolved.split_at(prefix_length);
    if imported.eq_ignore_ascii_case(written) && (prefix.is_empty() || prefix.ends_with(b"\\")) {
        Some(concat_word!(prefix, written))
    } else {
        None
    }
}

pub fn check_type_hint_casing<'arena, A>(context: &mut Context<'_, 'arena, A>, program: &Program<'arena>)
where
    A: Arena,
{
    TypeHintCasingWalker.walk_program(program, context);
}

struct TypeHintCasingWalker;

impl<'ast, 'ctx, 'arena, A> Walker<'ast, 'arena, Context<'ctx, 'arena, A>> for TypeHintCasingWalker
where
    A: Arena,
{
    fn walk_in_hint(&self, hint: &'ast Hint<'arena>, context: &mut Context<'ctx, 'arena, A>) {
        if let Hint::Identifier(identifier) = hint {
            let resolved = context.resolved_names.get(identifier);

            if let Some(written) = written_class_name(resolved, identifier.value()) {
                check_class_like_casing(context, written, identifier.span());
            }
        }
    }
}

/// Checks if the used function name matches the canonical casing and reports if not.
pub fn check_function_casing_with_metadata<A>(
    context: &mut Context<'_, '_, A>,
    metadata: &FunctionLikeMetadata,
    used_name: Word,
    span: Span,
) where
    A: Arena,
{
    if !context.settings.check_name_casing {
        return;
    }

    let canonical = metadata.original_name;

    if canonical == used_name {
        return;
    }

    let canonical_bytes = canonical.as_bytes();
    let used_bytes = used_name.as_bytes();
    if memchr::memchr(b'\\', canonical_bytes).is_none()
        && let Some(idx) = memchr::memrchr(b'\\', used_bytes)
        && &used_bytes[idx + 1..] == canonical_bytes
    {
        return;
    }

    context.collector.report_with_code(
        IssueCode::IncorrectFunctionCasing,
        Issue::warning(format!("Incorrect casing for function `{used_name}`."))
            .with_annotation(
                Annotation::primary(span).with_message(format!("Used as `{used_name}`, but defined as `{canonical}`.")),
            )
            .with_help(format!("Use the correct casing: `{canonical}`.")),
    );
}

pub fn check_method_call_casing<A>(
    context: &mut Context<'_, '_, A>,
    resolved_methods: &[ResolvedMethod],
    selector: &ClassLikeMemberSelector<'_>,
) where
    A: Arena,
{
    if !context.settings.check_name_casing {
        return;
    }

    let ClassLikeMemberSelector::Identifier(identifier) = selector else {
        return;
    };

    let used_name = word(identifier.value);
    let Some(method) = resolved_methods
        .iter()
        .filter_map(|resolved_method| context.codebase.get_method_by_id(&resolved_method.method_identifier))
        .find(|method| method.original_name != used_name)
    else {
        return;
    };

    let canonical = method.original_name;
    context.collector.report_with_code(
        IssueCode::IncorrectMethodCasing,
        Issue::warning(format!("Incorrect casing for method `{used_name}`."))
            .with_annotation(
                Annotation::primary(identifier.span)
                    .with_message(format!("Called as `{used_name}`, but defined as `{canonical}`.")),
            )
            .with_help(format!("Use the correct casing: `{canonical}`.")),
    );
}

pub fn check_inherited_method_casing<A>(
    context: &mut Context<'_, '_, A>,
    metadata: &ClassLikeMetadata,
    members: &[ClassLikeMember<'_>],
) where
    A: Arena,
{
    if !context.settings.check_name_casing || metadata.kind.is_trait() {
        return;
    }

    for member in members {
        let ClassLikeMember::Method(method) = member else {
            continue;
        };

        let declared_name = word(method.name.value);
        let Some(overridden_method_ids) = metadata.overridden_method_ids.get(&declared_name.to_ascii_lowercase())
        else {
            continue;
        };

        let Some((parent_class, parent_method)) = overridden_method_ids.values().find_map(|parent_method_id| {
            let parent_method = context.codebase.get_method_by_id(parent_method_id)?;
            let parent_class = context.codebase.get_class_like(parent_method_id.get_class_name().as_bytes())?;

            (parent_method.original_name != declared_name).then_some((parent_class, parent_method))
        }) else {
            continue;
        };

        let class_name = metadata.original_name;
        let parent_class_name = parent_class.original_name;
        let parent_method_name = parent_method.original_name;
        context.collector.report_with_code(
            IssueCode::IncorrectMethodCasing,
            Issue::warning(format!(
                "Method `{class_name}::{declared_name}` does not match the casing of `{parent_class_name}::{parent_method_name}`."
            ))
            .with_annotation(
                Annotation::primary(method.name.span)
                    .with_message(format!("Declared as `{declared_name}`, but inherited as `{parent_method_name}`.")),
            )
            .with_help(format!("Use the inherited casing: `{parent_method_name}`.")),
        );
    }
}

/// Checks if the used function name matches the canonical casing and reports if not.
fn check_function_casing<A>(context: &mut Context<'_, '_, A>, used_name: Word, span: Span)
where
    A: Arena,
{
    if !context.settings.check_name_casing {
        return;
    }

    let Some(metadata) = context.codebase.get_function(used_name.as_bytes()) else {
        return;
    };

    check_function_casing_with_metadata(context, metadata, used_name, span);
}

/// Checks casing of all names imported via `use` statements.
pub fn check_use_statement_casing<A>(context: &mut Context<'_, '_, A>, r#use: &Use<'_>)
where
    A: Arena,
{
    match &r#use.items {
        UseItems::Sequence(sequence) => {
            for item in sequence.items.iter() {
                let fqn = word(mago_bytes::trim_start_byte(item.name.value(), b'\\'));
                check_class_like_casing(context, fqn, item.name.span());
            }
        }
        UseItems::TypedSequence(typed_sequence) => {
            for item in typed_sequence.items.iter() {
                let fqn = word(mago_bytes::trim_start_byte(item.name.value(), b'\\'));
                check_typed_use_casing(context, fqn, item.name.span(), &typed_sequence.r#type);
            }
        }
        UseItems::TypedList(typed_list) => {
            let prefix = mago_bytes::trim_start_byte(typed_list.namespace.value(), b'\\');
            for item in typed_list.items.iter() {
                let fqn = concat_word!(prefix, b"\\", item.name.value());
                check_typed_use_casing(context, fqn, item.name.span(), &typed_list.r#type);
            }
        }
        UseItems::MixedList(mixed_list) => {
            let prefix = mago_bytes::trim_start_byte(mixed_list.namespace.value(), b'\\');
            for maybe_typed_item in mixed_list.items.iter() {
                let fqn = concat_word!(prefix, b"\\", maybe_typed_item.item.name.value());
                match &maybe_typed_item.r#type {
                    Some(UseType::Function(_)) => {
                        check_function_casing(context, fqn, maybe_typed_item.item.name.span())
                    }
                    Some(UseType::Const(_)) => {} // Constants are case-sensitive
                    None => check_class_like_casing(context, fqn, maybe_typed_item.item.name.span()),
                }
            }
        }
    }
}

fn check_typed_use_casing<A>(context: &mut Context<'_, '_, A>, fqn: Word, span: Span, use_type: &UseType<'_>)
where
    A: Arena,
{
    match use_type {
        UseType::Function(_) => check_function_casing(context, fqn, span),
        UseType::Const(_) => {} // Constants are case-sensitive
    }
}
