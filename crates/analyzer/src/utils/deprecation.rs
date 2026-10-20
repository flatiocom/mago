use mago_allocator::Arena;
use mago_codex::identifier::function_like::FunctionLikeIdentifier;
use mago_codex::identifier::method::MethodIdentifier;
use mago_codex::metadata::class_like::ClassLikeMetadata;
use mago_codex::metadata::class_like_constant::ClassLikeConstantMetadata;
use mago_codex::metadata::function_like::FunctionLikeMetadata;
use mago_codex::metadata::property::PropertyMetadata;
use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::union::TUnion;
use mago_reporting::Annotation;
use mago_reporting::Issue;
use mago_span::Span;
use mago_word::Word;
use mago_word::word;

use crate::code::IssueCode;
use crate::context::Context;
use crate::invocation::post_process::display_callable_name;

pub fn report_deprecated_function_like<A>(
    context: &mut Context<'_, '_, A>,
    identifier: &FunctionLikeIdentifier,
    metadata: &FunctionLikeMetadata,
    static_class: Option<&ClassLikeMetadata>,
    span: Span,
) where
    A: Arena,
{
    let method_name = metadata.original_name;
    if let FunctionLikeIdentifier::Method(declaring_class, _) = identifier
        && let Some(declaring_class) = get_deprecated_class_like(context, *declaring_class)
    {
        let class_name = declaring_class.original_name;
        let kind = declaring_class.kind.as_str();
        report(
            context,
            IssueCode::DeprecatedMethod,
            format!("Call to method {method_name}() of deprecated {kind} `{class_name}`."),
            span,
            format!("The {kind} declaring this method is deprecated"),
        );

        return;
    }

    if metadata.flags.is_deprecated() {
        let (code, kind) = match identifier {
            FunctionLikeIdentifier::Function(_) => (IssueCode::DeprecatedFunction, "function"),
            FunctionLikeIdentifier::Method(_, _) => (IssueCode::DeprecatedMethod, "method"),
            FunctionLikeIdentifier::Closure(_) => (IssueCode::DeprecatedClosure, "closure"),
        };
        let name = display_callable_name(context, identifier, metadata.original_name);
        report(
            context,
            code,
            format!("Call to deprecated {kind}: {name}."),
            span,
            format!("This {kind} is deprecated"),
        );

        return;
    }

    if let Some(static_class) = static_class.filter(|class| is_deprecated_class_like(class)) {
        let class_name = static_class.original_name;
        let kind = static_class.kind.as_str();
        report(
            context,
            IssueCode::DeprecatedMethod,
            format!("Call to static method {method_name}() on deprecated {kind} `{class_name}`."),
            span,
            format!("This {kind} is deprecated"),
        );
    }
}

pub fn report_deprecated_property<A>(
    context: &mut Context<'_, '_, A>,
    declaring_class: &ClassLikeMetadata,
    property: &PropertyMetadata,
    span: Span,
) where
    A: Arena,
{
    let property_name = property.name.0;
    if is_deprecated_class_like(declaring_class) {
        let class_name = declaring_class.original_name;
        let kind = declaring_class.kind.as_str();
        let static_modifier = if property.flags.is_static() { "static " } else { "" };
        report(
            context,
            IssueCode::DeprecatedProperty,
            format!("Access to {static_modifier}property {property_name} of deprecated {kind} `{class_name}`."),
            span,
            format!("The {kind} declaring this property is deprecated"),
        );

        return;
    }

    if property.flags.is_deprecated() {
        let name = format!("{}::{property_name}", declaring_class.original_name);
        report(
            context,
            IssueCode::DeprecatedProperty,
            format!("Use of deprecated property `{name}`."),
            span,
            "This property is deprecated".to_string(),
        );
    }
}

pub fn report_static_property_on_deprecated_class<A>(
    context: &mut Context<'_, '_, A>,
    named_class: &ClassLikeMetadata,
    declaring_class: &ClassLikeMetadata,
    property: &PropertyMetadata,
    span: Span,
) where
    A: Arena,
{
    if property.flags.is_deprecated()
        || is_deprecated_class_like(declaring_class)
        || !is_deprecated_class_like(named_class)
    {
        return;
    }

    let class_name = named_class.original_name;
    let kind = named_class.kind.as_str();
    report(
        context,
        IssueCode::DeprecatedProperty,
        format!("Access to static property {} on deprecated {kind} `{class_name}`.", property.name.0),
        span,
        format!("This {kind} is deprecated"),
    );
}

pub fn report_deprecated_class_constant<A>(
    context: &mut Context<'_, '_, A>,
    class: &ClassLikeMetadata,
    constant_name: Word,
    constant_span: Option<Span>,
    is_constant_deprecated: bool,
    named: bool,
    span: Span,
) where
    A: Arena,
{
    let declaring_class =
        constant_span.and_then(|constant_span| get_declaring_class_like(context, class, constant_span));
    if let Some(declaring_class) = declaring_class.filter(|declaring_class| is_deprecated_class_like(declaring_class)) {
        let class_name = declaring_class.original_name;
        let kind = declaring_class.kind.as_str();
        report(
            context,
            IssueCode::DeprecatedClassConstant,
            format!("Fetching class constant {constant_name} of deprecated {kind} `{class_name}`."),
            span,
            format!("The {kind} declaring this constant is deprecated"),
        );

        return;
    }

    if is_constant_deprecated {
        let name = format!("{}::{constant_name}", class.original_name);
        report(
            context,
            IssueCode::DeprecatedClassConstant,
            format!("Use of deprecated class constant `{name}`."),
            span,
            "This class constant is deprecated".to_string(),
        );

        return;
    }

    if named && is_deprecated_class_like(class) {
        let class_name = class.original_name;
        let kind = class.kind.as_str();
        report(
            context,
            IssueCode::DeprecatedClassConstant,
            format!("Access to constant {constant_name} on deprecated {kind} `{class_name}`."),
            span,
            format!("This {kind} is deprecated"),
        );
    }
}

pub fn report_deprecated_class_constant_metadata<A>(
    context: &mut Context<'_, '_, A>,
    class: &ClassLikeMetadata,
    constant: &ClassLikeConstantMetadata,
    named: bool,
    span: Span,
) where
    A: Arena,
{
    report_deprecated_class_constant(
        context,
        class,
        constant.name,
        Some(constant.span),
        constant.flags.is_deprecated(),
        named,
        span,
    );
}

pub fn report_class_name_of_deprecated_class<A>(context: &mut Context<'_, '_, A>, class_name: Word, span: Span)
where
    A: Arena,
{
    let Some(class) = get_deprecated_class_like(context, class_name) else {
        return;
    };

    let class_name = class.original_name;
    let kind = class.kind.as_str();
    report(
        context,
        IssueCode::DeprecatedClassConstant,
        format!("Access to constant on deprecated {kind} `{class_name}`."),
        span,
        format!("This {kind} is deprecated"),
    );
}

pub fn report_deprecated_string_cast<A>(context: &mut Context<'_, '_, A>, operand_type: &TUnion, span: Span)
where
    A: Arena,
{
    for atomic in operand_type.types.iter() {
        let TAtomic::Object(object) = atomic else {
            continue;
        };

        let Some(class_name) = object.get_name() else {
            continue;
        };

        let to_string =
            context.codebase.get_declaring_method_identifier(&MethodIdentifier::new(class_name, word("__toString")));
        let Some(metadata) = context.codebase.get_method_by_id(&to_string) else {
            continue;
        };

        let identifier = FunctionLikeIdentifier::Method(to_string.get_class_name(), to_string.get_method_name());
        report_deprecated_function_like(context, &identifier, metadata, None, span);
    }
}

fn is_deprecated_class_like(class: &ClassLikeMetadata) -> bool {
    class.flags.is_deprecated() && !class.kind.is_trait()
}

fn get_deprecated_class_like<'ctx, A>(
    context: &Context<'ctx, '_, A>,
    class_name: Word,
) -> Option<&'ctx ClassLikeMetadata>
where
    A: Arena,
{
    context.codebase.get_class_like(class_name.as_bytes()).filter(|class| is_deprecated_class_like(class))
}

fn get_declaring_class_like<'ctx, A>(
    context: &Context<'ctx, '_, A>,
    class: &'ctx ClassLikeMetadata,
    member_span: Span,
) -> Option<&'ctx ClassLikeMetadata>
where
    A: Arena,
{
    let declares_member = |candidate: &ClassLikeMetadata| {
        candidate.span.file_id == member_span.file_id && candidate.span.has_offset(member_span.start.offset)
    };

    if declares_member(class) {
        return Some(class);
    }

    class
        .all_parent_classes
        .iter()
        .chain(&class.all_parent_interfaces)
        .filter_map(|name| context.codebase.get_class_like(name.as_bytes()))
        .find(|candidate| declares_member(candidate))
}

fn report<A>(context: &mut Context<'_, '_, A>, code: IssueCode, message: String, span: Span, annotation: String)
where
    A: Arena,
{
    context.collector.report_with_code(
        code,
        Issue::warning(message)
            .with_annotation(Annotation::primary(span).with_message(annotation))
            .with_help("Consult the documentation of the deprecated symbol for its replacement."),
    );
}
