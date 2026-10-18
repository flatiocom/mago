use mago_allocator::Arena;
use mago_codex::metadata::CodebaseMetadata;
use mago_codex::metadata::class_like::ClassLikeMetadata;
use mago_codex::metadata::function_like::FunctionLikeKind;
use mago_codex::metadata::function_like::FunctionLikeMetadata;
use mago_codex::metadata::property::PropertyMetadata;
use mago_codex::metadata::ttype::TypeMetadata;
use mago_codex::ttype::TType;
use mago_codex::ttype::TypeRef;
use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::atomic::callable::TCallable;
use mago_codex::ttype::atomic::callable::TCallableSignature;
use mago_codex::ttype::atomic::object::TObject;
use mago_codex::ttype::union::TUnion;
use mago_php_version::PHPVersion;
use mago_php_version::feature::Feature;
use mago_reporting::Annotation;
use mago_reporting::Issue;
use mago_span::HasSpan;
use mago_span::Span;
use mago_syntax::cst::ClassLikeConstant;
use mago_syntax::cst::FunctionLikeParameter;
use mago_syntax::cst::FunctionLikeReturnTypeHint;
use mago_syntax::cst::Hint;
use mago_syntax::cst::Property;

use crate::code::IssueCode;
use crate::context::Context;
use mago_bytes::BytesDisplay;

/// Check if a constant is missing a type hint and whether it's safe to add one.
///
/// A constant should only be reported as missing a type hint if:
/// 1. It has no type hint
/// 2. Typed class constants are supported in the target PHP version
pub fn check_constant_type_hint<'arena, A>(
    context: &mut Context<'_, 'arena, A>,
    class_like_constant: &ClassLikeConstant<'arena>,
) where
    A: Arena,
{
    if !context.settings.check_missing_type_hints
        || !context.settings.version.is_supported(Feature::TypedClassLikeConstants)
    {
        return;
    }

    if class_like_constant.hint.is_some() {
        return;
    }

    let item = class_like_constant.first_item();

    let constant_name = BytesDisplay(item.name.value);

    context.collector.report_with_code(
        IssueCode::MissingConstantType,
        Issue::warning(format!("Class constant `{constant_name}` is missing a type hint."))
            .with_annotation(
                Annotation::primary(class_like_constant.span())
                    .with_message(format!("Class constant `{constant_name}` is defined here")),
            )
            .with_note("Adding a type hint to constants improves code readability and helps prevent type errors.")
            .with_help(format!("Consider specifying a type hint for `{constant_name}`.")),
    );
}

/// Check if a property is missing a type hint and whether it's safe to add one.
///
/// A property should only be reported as missing a type hint if:
/// 1. It has no type hint
/// 2. It is not prefixed with `$_` (ignored by convention)
/// 3. It would be safe to add a type hint (i.e., no parent class/trait has the same property without a type hint)
/// 4. Typed properties are supported in the target PHP version
pub fn check_property_type_hint<'arena, A>(
    context: &mut Context<'_, 'arena, A>,
    class_like_metadata: &ClassLikeMetadata,
    property: &Property<'arena>,
) where
    A: Arena,
{
    if !context.settings.check_missing_type_hints || !context.settings.version.is_supported(Feature::TypedProperties) {
        return;
    }

    // If it already has a type hint, nothing to check
    if property.hint().is_some() {
        return;
    }

    for variable in property.variables() {
        // Skip variables prefixed with `$_`
        if variable.name.starts_with(b"$_") {
            continue;
        }

        let docblock_type = class_like_metadata
            .properties
            .get(&mago_word::word(variable.name))
            .and_then(|property| property.type_metadata.as_ref());
        if !has_native_equivalent(docblock_type, NativeHintPosition::Property) {
            continue;
        }

        // Check if it's safe to add a type hint by verifying no parent class/trait has
        // the same property without a type hint
        if is_safe_to_add_property_type_hint(context, class_like_metadata, variable.name) {
            let variable_name = BytesDisplay(variable.name);
            context.collector.report(
                Issue::warning(format!("Property `{variable_name}` is missing a type hint."))
                    .with_code(IssueCode::MissingPropertyType.as_str())
                    .with_annotation(
                        Annotation::primary(property.span())
                            .with_message(format!("Property `{variable_name}` declared here without a type hint")),
                    )
                    .with_note(
                        "Adding type hints to properties improves code readability and helps prevent type errors.",
                    )
                    .with_help(format!("Consider adding a type hint to property `{variable_name}`.")),
            );
        }
    }
}

/// Check if a parameter is missing a type hint.
///
/// A parameter should only be reported as missing a type hint if:
/// 1. It has no type hint
/// 2. It is not prefixed with `$_` (ignored by convention)
/// 3. The method is not overriding a parent method (where adding a type hint might cause issues)
/// 4. If it's a closure/arrow function parameter, `check_closure_and_arrow_function_parameter_type_hints`
///    is enabled, or `check_missing_type_hints` is enabled together with the setting for its kind
/// 5. Otherwise, `check_missing_type_hints` is enabled
/// 6. Typed parameters are supported in the target PHP version
pub fn check_parameter_type_hint<'arena, A>(
    context: &mut Context<'_, 'arena, A>,
    class_like_metadata: Option<&ClassLikeMetadata>,
    function_like_metadata: &FunctionLikeMetadata,
    parameter: &FunctionLikeParameter<'arena>,
) where
    A: Arena,
{
    if context.settings.version < PHPVersion::PHP70 {
        return;
    }

    // If it already has a type hint, nothing to check
    if parameter.hint.is_some() || parameter.variable.name.starts_with(b"$_") {
        return;
    }

    let settings = &context.settings;
    let enabled = match function_like_metadata.kind {
        FunctionLikeKind::Closure => {
            settings.check_closure_and_arrow_function_parameter_type_hints
                || (settings.check_missing_type_hints && settings.check_closure_missing_type_hints)
        }
        FunctionLikeKind::ArrowFunction => {
            settings.check_closure_and_arrow_function_parameter_type_hints
                || (settings.check_missing_type_hints && settings.check_arrow_function_missing_type_hints)
        }
        _ => settings.check_missing_type_hints,
    };

    if !enabled {
        return;
    }

    let position =
        if parameter.is_promoted_property() { NativeHintPosition::Property } else { NativeHintPosition::Signature };
    let docblock_type = function_like_metadata
        .get_parameter(mago_word::word(parameter.variable.name))
        .and_then(|parameter| parameter.type_metadata.as_ref());
    if !has_native_equivalent(docblock_type, position) {
        return;
    }

    // If this is a method, check if it's safe to add a type hint
    if let Some(class_metadata) = class_like_metadata
        && !is_safe_to_add_parameter_type_hint(context, class_metadata, function_like_metadata)
    {
        return;
    }

    let parameter_name = BytesDisplay(parameter.variable.name);
    context.collector.report(
        Issue::warning(format!("Parameter `{parameter_name}` is missing a type hint."))
            .with_code(IssueCode::MissingParameterType.as_str())
            .with_annotation(
                Annotation::primary(parameter.span())
                    .with_message(format!("Parameter `{parameter_name}` declared here without a type hint")),
            )
            .with_note("Type hints improve code readability and help prevent type-related errors.")
            .with_help(format!("Consider adding a type hint to parameter `{parameter_name}`.")),
    );
}

/// Check if a function or method is missing a return type hint.
///
/// A function/method should only be reported as missing a return type hint if:
/// 1. It has no return type hint
/// 2. It's not a constructor or destructor
/// 3. If it's a method, it's not overriding a parent method
/// 4. If it's a closure/arrow function, the corresponding ignore setting is not enabled
/// 5. Return type hints are supported in the target PHP version
pub fn check_return_type_hint<'arena, A>(
    context: &mut Context<'_, 'arena, A>,
    class_like_metadata: Option<&ClassLikeMetadata>,
    function_like_metadata: &FunctionLikeMetadata,
    function_name: &[u8],
    return_type_hint: Option<&FunctionLikeReturnTypeHint<'arena>>,
    span: Span,
) where
    A: Arena,
{
    if !context.settings.check_missing_type_hints || context.settings.version < PHPVersion::PHP70 {
        return;
    }

    // If it already has a return type hint, nothing to check
    if return_type_hint.is_some() {
        return;
    }

    // Check if we should skip based on function kind
    if matches!(function_like_metadata.kind, FunctionLikeKind::Closure)
        && !context.settings.check_closure_missing_type_hints
    {
        return;
    }
    if matches!(function_like_metadata.kind, FunctionLikeKind::ArrowFunction)
        && !context.settings.check_arrow_function_missing_type_hints
    {
        return;
    }

    // Skip constructors and destructors
    if function_name == b"__construct" || function_name == b"__destruct" {
        return;
    }

    if !has_native_equivalent(function_like_metadata.return_type_metadata.as_ref(), NativeHintPosition::Signature) {
        return;
    }

    // If this is a method, check if it's safe to add a return type hint
    if let Some(class_metadata) = class_like_metadata
        && !is_safe_to_add_return_type_hint(context, class_metadata, function_like_metadata)
    {
        return;
    }

    let function_name = BytesDisplay(function_name);
    context.collector.report(
        Issue::warning(format!("Function `{function_name}` is missing a return type hint."))
            .with_code(IssueCode::MissingReturnType.as_str())
            .with_annotation(
                Annotation::primary(span)
                    .with_message(format!("Function `{function_name}` declared here without a return type hint")),
            )
            .with_note("Return type hints improve code readability and help prevent type-related errors.")
            .with_help(format!("Consider adding a return type hint to function `{function_name}`.")),
    );
}

/// Check if a return type hint uses a bare `array` or `iterable` without a more specific
/// docblock annotation.
pub fn check_imprecise_return_type_hint<'arena, A>(
    context: &mut Context<'_, 'arena, A>,
    function_like_metadata: &FunctionLikeMetadata,
    function_name: &[u8],
    return_type_hint: Option<&FunctionLikeReturnTypeHint<'arena>>,
    declaration: Option<Span>,
) where
    A: Arena,
{
    if !context.settings.check_missing_type_hints {
        return;
    }

    if matches!(function_like_metadata.kind, FunctionLikeKind::Closure)
        && !context.settings.check_closure_missing_type_hints
    {
        return;
    }

    if matches!(function_like_metadata.kind, FunctionLikeKind::ArrowFunction)
        && !context.settings.check_arrow_function_missing_type_hints
    {
        return;
    }

    let Some(return_type_hint) = return_type_hint else {
        return;
    };

    if function_like_metadata.return_type_metadata.as_ref().is_some_and(|m| m.from_docblock) {
        return;
    }

    let function_name = BytesDisplay(function_name);
    for (type_name, span) in collect_imprecise_hints(&return_type_hint.hint) {
        report_imprecise_signature_type(
            context,
            type_name,
            span,
            &format!("return type of `{function_name}`"),
            declaration,
        );
    }
}

/// Check if a parameter type hint uses a bare `array` or `iterable` without a more specific
/// docblock annotation.
pub fn check_imprecise_parameter_type_hint<'arena, A>(
    context: &mut Context<'_, 'arena, A>,
    function_like_metadata: &FunctionLikeMetadata,
    parameter: &FunctionLikeParameter<'arena>,
    parameter_index: usize,
    declaration: Option<Span>,
) where
    A: Arena,
{
    if !context.settings.check_missing_type_hints {
        return;
    }

    // Skip closures/arrow functions based on settings
    if matches!(function_like_metadata.kind, FunctionLikeKind::Closure)
        && !context.settings.check_closure_missing_type_hints
    {
        return;
    }
    if matches!(function_like_metadata.kind, FunctionLikeKind::ArrowFunction)
        && !context.settings.check_arrow_function_missing_type_hints
    {
        return;
    }

    let Some(hint) = &parameter.hint else {
        return;
    };

    // If the docblock provides a more specific type, skip
    if let Some(param_meta) = function_like_metadata.parameters.get(parameter_index)
        && param_meta.type_metadata.as_ref().is_some_and(|m| m.from_docblock)
    {
        return;
    }

    let parameter_name = BytesDisplay(parameter.variable.name);
    for (type_name, span) in collect_imprecise_hints(hint) {
        report_imprecise_signature_type(
            context,
            type_name,
            span,
            &format!("parameter `{parameter_name}`"),
            declaration,
        );
    }
}

/// Check if a property type hint uses a bare `array` or `iterable` without a more specific
/// docblock annotation.
pub fn check_imprecise_property_type_hint<'arena, A>(
    context: &mut Context<'_, 'arena, A>,
    property: &Property<'arena>,
    property_metadata: Option<&PropertyMetadata>,
) where
    A: Arena,
{
    if !context.settings.check_missing_type_hints {
        return;
    }

    let Some(hint) = property.hint() else {
        return;
    };

    // If the docblock provides a more specific type, skip
    if let Some(prop_meta) = property_metadata
        && prop_meta.type_metadata.as_ref().is_some_and(|m| m.from_docblock)
    {
        return;
    }

    let imprecise = collect_imprecise_hints(hint);
    if imprecise.is_empty() {
        return;
    }

    for variable in property.variables() {
        let variable_name = BytesDisplay(variable.name);
        for &(type_name, span) in &imprecise {
            report_imprecise_type(context, type_name, span, &format!("property `{variable_name}`"));
        }
    }
}

pub fn check_function_like_callable_signatures<A>(
    context: &mut Context<'_, '_, A>,
    function_like_metadata: &FunctionLikeMetadata,
    name_span: Span,
    function_label: &str,
) where
    A: Arena,
{
    for parameter in &function_like_metadata.parameters {
        check_callable_signatures(
            context,
            parameter.type_metadata.as_ref(),
            parameter.span,
            &format!("Parameter `{}` of {function_label}", parameter.name.0),
        );
    }

    check_callable_signatures(
        context,
        function_like_metadata.return_type_metadata.as_ref(),
        name_span,
        &format!("Return type of {function_label}"),
    );
}

pub fn check_callable_signatures<A>(
    context: &mut Context<'_, '_, A>,
    type_metadata: Option<&TypeMetadata>,
    span: Span,
    subject: &str,
) where
    A: Arena,
{
    if !context.settings.check_missing_type_hints {
        return;
    }

    let Some(type_metadata) = type_metadata.filter(|type_metadata| !type_metadata.inferred) else {
        return;
    };

    for node in type_metadata.type_union.get_all_child_nodes() {
        let TypeRef::Atomic(atomic) = node else {
            continue;
        };

        let Some(callable_name) = unsigned_callable_name(atomic) else {
            continue;
        };

        context.collector.report_with_code(
            IssueCode::MissingCallableSignature,
            Issue::warning(format!("{subject} has no signature specified for `{callable_name}`."))
                .with_annotation(Annotation::primary(span).with_message(format!("`{callable_name}` without a signature")))
                .with_note(format!(
                    "A bare `{callable_name}` accepts any arguments and returns `mixed`, so its calls cannot be checked."
                ))
                .with_help(format!("Specify the signature in a docblock annotation, e.g. `{callable_name}(int): string`.")),
        );
    }
}

fn unsigned_callable_name(atomic: &TAtomic) -> Option<&'static str> {
    match atomic {
        TAtomic::Callable(TCallable::Signature(signature)) if is_unsigned(signature) => {
            Some(if signature.is_closure { "Closure" } else { "callable" })
        }
        TAtomic::Object(TObject::Named(object))
            if object.type_parameters.is_none() && object.name.as_bytes().eq_ignore_ascii_case(b"Closure") =>
        {
            Some("Closure")
        }
        _ => None,
    }
}

fn is_unsigned(signature: &TCallableSignature) -> bool {
    signature.source.is_none()
        && !signature.is_partial
        && signature.get_return_type().is_some_and(|return_type| return_type.is_mixed())
        && matches!(
            signature.get_parameters(),
            [parameter] if parameter.is_variadic() && parameter.get_type_signature().is_some_and(|parameter_type| parameter_type.is_mixed())
        )
}

pub fn check_function_like_template_arguments<A>(
    context: &mut Context<'_, '_, A>,
    function_like_metadata: &FunctionLikeMetadata,
    name_span: Span,
    function_label: &str,
) where
    A: Arena,
{
    for parameter in &function_like_metadata.parameters {
        check_template_arguments(
            context,
            parameter.type_metadata.as_ref(),
            parameter.span,
            &format!("Parameter `{}` of {function_label}", parameter.name.0),
        );
    }

    check_template_arguments(
        context,
        function_like_metadata.return_type_metadata.as_ref(),
        name_span,
        &format!("Return type of {function_label}"),
    );
}

pub fn check_template_arguments<A>(
    context: &mut Context<'_, '_, A>,
    type_metadata: Option<&TypeMetadata>,
    span: Span,
    subject: &str,
) where
    A: Arena,
{
    if !context.settings.check_missing_type_hints {
        return;
    }

    let Some(type_metadata) = type_metadata.filter(|type_metadata| !type_metadata.inferred) else {
        return;
    };

    report_missing_template_arguments(context, &type_metadata.type_union, span, subject);
}

pub(crate) fn report_missing_template_arguments<A>(
    context: &mut Context<'_, '_, A>,
    type_union: &TUnion,
    span: Span,
    subject: &str,
) where
    A: Arena,
{
    for generic_class in generic_classes_without_arguments(context.codebase, type_union) {
        let kind = generic_class.kind.as_str();
        let name = generic_class.original_name;
        let template_list = generic_class
            .template_types
            .keys()
            .map(|template_name| template_name.to_string())
            .collect::<Vec<_>>()
            .join(", ");

        context.collector.report_with_code(
            IssueCode::MissingTemplateParameter,
            Issue::warning(format!(
                "{subject} uses generic {kind} `{name}` without its template arguments: {template_list}."
            ))
            .with_annotation(Annotation::primary(span).with_message(format!("`{name}` without template arguments")))
            .with_note(format!("Without template arguments, the template types of `{name}` fall back to their bounds."))
            .with_help(format!("Specify the template arguments in a docblock annotation, e.g. `{name}<...>`.")),
        );
    }
}

fn generic_classes_without_arguments<'codebase>(
    codebase: &'codebase CodebaseMetadata,
    type_union: &TUnion,
) -> Vec<&'codebase ClassLikeMetadata> {
    let mut generic_classes = Vec::new();
    let mut nodes = type_union.get_child_nodes();

    while let Some(node) = nodes.pop() {
        let atomic = match node {
            TypeRef::Union(union) => {
                union.append_child_nodes(&mut nodes);
                continue;
            }
            TypeRef::Atomic(TAtomic::GenericParameter(_)) => continue,
            TypeRef::Atomic(atomic) => atomic,
        };

        if let TAtomic::Object(TObject::Named(object)) = atomic
            && object
                .type_parameters
                .as_deref()
                .is_none_or(|parameters| parameters.iter().all(TUnion::from_template_fallback))
            && !object.is_static
            && let Some(class_like_metadata) = codebase.get_class_like(object.name.as_bytes())
            && requires_template_arguments(class_like_metadata)
        {
            generic_classes.push(class_like_metadata);
        }

        atomic.append_child_nodes(&mut nodes);
    }

    generic_classes
}

fn requires_template_arguments(class_like_metadata: &ClassLikeMetadata) -> bool {
    !class_like_metadata.kind.is_trait()
        && !class_like_metadata.original_name.as_bytes().eq_ignore_ascii_case(b"Traversable")
        && class_like_metadata.template_types.values().any(|template| template.default.is_none())
}

/// Collect all bare `array` or `iterable` hints from a type hint, recursing into
/// unions, intersections, nullable, and parenthesized types.
fn collect_imprecise_hints(hint: &Hint<'_>) -> Vec<(&'static str, Span)> {
    let mut results = vec![];
    collect_imprecise_hints_inner(hint, &mut results);
    results
}

fn collect_imprecise_hints_inner(hint: &Hint<'_>, results: &mut Vec<(&'static str, Span)>) {
    match hint {
        Hint::Array(keyword) => {
            results.push(("array", keyword.span()));
        }
        Hint::Iterable(identifier) => {
            results.push(("iterable", identifier.span()));
        }
        Hint::Nullable(nullable) => {
            collect_imprecise_hints_inner(nullable.hint, results);
        }
        Hint::Union(union) => {
            collect_imprecise_hints_inner(union.left, results);
            collect_imprecise_hints_inner(union.right, results);
        }
        Hint::Intersection(intersection) => {
            collect_imprecise_hints_inner(intersection.left, results);
            collect_imprecise_hints_inner(intersection.right, results);
        }
        Hint::Parenthesized(parenthesized) => {
            collect_imprecise_hints_inner(parenthesized.hint, results);
        }
        _ => {}
    }
}

/// A function or method signature is reported from its first line, where PHPStan reports `missingType.iterableValue`.
fn report_imprecise_signature_type<A>(
    context: &mut Context<'_, '_, A>,
    type_name: &str,
    span: Span,
    location: &str,
    declaration: Option<Span>,
) where
    A: Arena,
{
    let Some(declaration) = declaration else {
        report_imprecise_type(context, type_name, span, location);
        return;
    };

    let issue = imprecise_type_issue(type_name, location)
        .with_annotation(Annotation::primary(declaration).with_message("declared here"))
        .with_annotation(Annotation::secondary(span).with_message(format!("imprecise `{type_name}` type hint")));
    context.collector.report_with_code(IssueCode::ImpreciseType, issue);
}

pub(crate) fn report_imprecise_type<A>(context: &mut Context<'_, '_, A>, type_name: &str, span: Span, location: &str)
where
    A: Arena,
{
    let issue = imprecise_type_issue(type_name, location)
        .with_annotation(Annotation::primary(span).with_message(format!("imprecise `{type_name}` type hint")));
    context.collector.report_with_code(IssueCode::ImpreciseType, issue);
}

fn imprecise_type_issue(type_name: &str, location: &str) -> Issue {
    // `iterable` can have any key type (not just array-key), since iterators support arbitrary keys.
    let equivalent = match type_name {
        "iterable" => "iterable<mixed, mixed>",
        "list" | "non-empty-list" => "list<mixed>",
        _ => "array<array-key, mixed>",
    };

    Issue::warning(format!("Type `{type_name}` in {location} is imprecise, equivalent to `{equivalent}`."))
        .with_note(format!("Bare `{type_name}` does not specify key or value types, making it difficult for the analyzer to verify correctness."))
        .with_help(format!(
            "Specify a more precise type in a docblock annotation (e.g., `{type_name}<string, int>`, `list<Foo>`), or use `{equivalent}` to be explicit."
        ))
}

#[derive(Clone, Copy, PartialEq, Eq)]
enum NativeHintPosition {
    Signature,
    Property,
}

fn has_native_equivalent(docblock_type: Option<&TypeMetadata>, position: NativeHintPosition) -> bool {
    let Some(docblock_type) = docblock_type.filter(|type_metadata| type_metadata.from_docblock) else {
        return true;
    };

    !docblock_type.type_union.types.iter().any(|atomic| match atomic {
        TAtomic::Resource(_) => true,
        TAtomic::Callable(TCallable::Signature(signature)) => {
            position == NativeHintPosition::Property && !signature.is_closure
        }
        TAtomic::Callable(TCallable::Alias(_)) => position == NativeHintPosition::Property,
        _ => false,
    })
}

/// Check if it's safe to add a type hint to a property.
///
/// It's safe to add a property type hint if no parent class or trait declares the same property
/// without a type hint (because adding a type hint would create a compile error in PHP).
fn is_safe_to_add_property_type_hint<A>(
    context: &Context<'_, '_, A>,
    class_like_metadata: &ClassLikeMetadata,
    property_name: &[u8],
) -> bool
where
    A: Arena,
{
    let property_word = mago_word::word(property_name);

    // Check all parent classes
    for parent_name in &class_like_metadata.all_parent_classes {
        if let Some(parent_metadata) = context.codebase.get_class_like(parent_name.as_bytes()) {
            // If parent has this property
            if parent_metadata.properties.contains_key(&property_word) {
                return false;
            }
        }
    }

    // Check all used traits
    for trait_name in &class_like_metadata.used_traits {
        if let Some(trait_metadata) = context.codebase.get_class_like(trait_name.as_bytes())
            && trait_metadata.properties.contains_key(&property_word)
        {
            return false;
        }
    }

    true
}

/// Check if it's safe to add a type hint to a parameter.
///
/// It's safe to add a parameter type hint if the method is not overriding a parent method
/// that has no type hints on the corresponding parameter.
fn is_safe_to_add_parameter_type_hint<A>(
    context: &Context<'_, '_, A>,
    class_like_metadata: &ClassLikeMetadata,
    function_like_metadata: &FunctionLikeMetadata,
) -> bool
where
    A: Arena,
{
    // If it's not a method, it's always safe
    if !matches!(function_like_metadata.kind, FunctionLikeKind::Method) {
        return true;
    }

    // Check if this method is overriding a parent method
    if context
        .codebase
        .method_is_overriding(class_like_metadata.name.as_bytes(), function_like_metadata.name.as_bytes())
    {
        // If overriding, we need to be conservative and not report
        // because we'd need to check if all parameters in the parent have type hints
        return false;
    }

    true
}

/// Check if it's safe to add a return type hint.
///
/// It's safe to add a return type hint if the method is not overriding a parent method
/// that has no return type hint.
fn is_safe_to_add_return_type_hint<A>(
    context: &Context<'_, '_, A>,
    class_like_metadata: &ClassLikeMetadata,
    function_like_metadata: &FunctionLikeMetadata,
) -> bool
where
    A: Arena,
{
    // If it's not a method, it's always safe
    if !matches!(function_like_metadata.kind, FunctionLikeKind::Method) {
        return true;
    }

    // Check if this method is overriding a parent method
    if context
        .codebase
        .method_is_overriding(class_like_metadata.name.as_bytes(), function_like_metadata.name.as_bytes())
    {
        // If overriding, we need to be conservative and not report
        return false;
    }

    true
}
