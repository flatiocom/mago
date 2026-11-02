use mago_allocator::Arena;
use mago_codex::identifier::function_like::FunctionLikeIdentifier;
use mago_codex::identifier::method::MethodIdentifier;
use mago_codex::metadata::function_like::FunctionLikeMetadata;
use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::atomic::array::key::ArrayKey;
use mago_codex::ttype::atomic::callable::TCallable;
use mago_codex::ttype::atomic::callable::TCallableSignature;
use mago_codex::ttype::atomic::callable::parameter::TCallableParameter;
use mago_codex::ttype::cast::cast_atomic_to_callable;
use mago_codex::ttype::expander::get_signature_of_function_like_identifier;
use mago_codex::ttype::template::inferred_type_replacer::replace_template_parameters_with_constraints;
use mago_reporting::Annotation;
use mago_reporting::Issue;
use mago_span::HasSpan;
use mago_syntax::cst::Argument;
use mago_syntax::cst::ArrayElement;
use mago_syntax::cst::Expression;
use mago_syntax::cst::FunctionCall;
use mago_syntax::cst::Literal;
use mago_word::Word;
use mago_word::ascii_lowercase_word;
use mago_word::word;

use crate::artifacts::AnalysisArtifacts;
use crate::code::IssueCode;
use crate::context::Context;
use crate::invocation::InvocationTarget;
use crate::invocation::arguments::verify_argument_type;

struct ForwardedCall<'arena> {
    function_name: &'static str,
    callback: &'arena Expression<'arena>,
    arguments: Vec<ForwardedArgument<'arena>>,
}

struct ForwardedArgument<'arena> {
    expression: &'arena Expression<'arena>,
    name: Option<Word>,
}

pub(super) fn check_call_user_func_arguments<'arena, A>(
    context: &mut Context<'_, 'arena, A>,
    artifacts: &AnalysisArtifacts,
    function_call: &FunctionCall<'arena>,
) where
    A: Arena,
{
    let Some(call) = get_forwarded_call(context, function_call) else {
        return;
    };

    let Some(signature) =
        artifacts.get_expression_type(call.callback).and_then(|callback_type| match callback_type.types.as_ref() {
            [callback_atomic] => get_callback_signature(context, callback_atomic),
            _ => None,
        })
    else {
        return;
    };

    if signature.has_no_signature() {
        return;
    }

    let values = &call.arguments;
    let has_named_values = values.iter().any(|value| value.name.is_some());

    if !has_named_values {
        report_argument_count(context, &signature, function_call, &call);
    }

    let target = InvocationTarget::Callable {
        source: signature.get_source(),
        signature: signature.clone(),
        effective_signature: None,
        span: call.callback.span(),
    };
    let parameters = signature.get_parameters();
    let forbids_named_arguments =
        get_callback_metadata(context, &signature).is_some_and(|metadata| metadata.flags.forbids_named_arguments());
    let mut passed_parameters = vec![false; parameters.len()];
    let mut named_value_passed = false;

    for (offset, value) in values.iter().enumerate() {
        let parameter_offset = match value.name {
            None if named_value_passed => continue,
            None => match parameters.get(offset).or_else(|| parameters.last().filter(|last| last.is_variadic())) {
                Some(_) => offset.min(parameters.len() - 1),
                None => break,
            },
            Some(name) => {
                named_value_passed = true;
                match find_named_parameter(context, &signature, name) {
                    Some(parameter_offset) => {
                        if forbids_named_arguments {
                            report_named_argument_not_allowed(context, &call, value.expression, name);
                        }

                        parameter_offset
                    }
                    None => {
                        report_unknown_parameter(context, &call, value.expression, name);
                        continue;
                    }
                }
            }
        };

        passed_parameters[parameter_offset] = true;

        let Some(parameter_type) = parameters[parameter_offset].get_type_signature() else {
            continue;
        };
        let Some(value_type) = artifacts.get_expression_type(value.expression) else {
            continue;
        };

        let parameter_type = replace_template_parameters_with_constraints(parameter_type, context.codebase);
        let argument_offset = if value.name.is_some() { parameter_offset } else { offset };
        verify_argument_type(context, value_type, &parameter_type, argument_offset, value.expression, &target);
    }

    if has_named_values {
        report_missing_parameters(context, parameters, &passed_parameters, function_call, &call);
    }
}

fn report_argument_count<A>(
    context: &mut Context<'_, '_, A>,
    signature: &TCallableSignature,
    function_call: &FunctionCall<'_>,
    call: &ForwardedCall<'_>,
) where
    A: Arena,
{
    let values = &call.arguments;
    let parameters = signature.get_parameters();
    let required_count =
        parameters.iter().filter(|parameter| !parameter.has_default() && !parameter.is_variadic()).count();
    let passed_count = values.len();
    let function_name = call.function_name;

    if passed_count < required_count {
        context.collector.report_with_code(
            IssueCode::TooFewArguments,
            Issue::error(format!("Too few arguments passed on by `{function_name}()` to its callback."))
                .with_annotation(
                    Annotation::primary(function_call.argument_list.span()).with_message("More arguments expected here"),
                )
                .with_annotation(Annotation::secondary(call.callback.span()).with_message("For this callback"))
                .with_note(format!(
                    "The callback requires {required_count} argument(s), but `{function_name}()` passes on {passed_count}."
                ))
                .with_help("Provide all required arguments."),
        );

        return;
    }

    let takes_any_count = parameters.last().is_some_and(|parameter| parameter.is_variadic())
        || get_callback_metadata(context, signature).is_some_and(|metadata| metadata.flags.uses_func_get_args());
    let Some(first_extra_value) = values.get(parameters.len()).filter(|_| !takes_any_count) else {
        return;
    };

    context.collector.report_with_code(
        IssueCode::TooManyArguments,
        Issue::error(format!("Too many arguments passed on by `{function_name}()` to its callback."))
            .with_annotation(
                Annotation::primary(first_extra_value.expression.span())
                    .with_message("Unexpected argument provided here"),
            )
            .with_annotation(Annotation::secondary(call.callback.span()).with_message("For this callback"))
            .with_note(format!(
                "The callback takes {} argument(s), but `{function_name}()` passes on {passed_count}.",
                parameters.len()
            ))
            .with_help("Remove the extra argument(s)."),
    );
}

fn find_named_parameter<A>(context: &Context<'_, '_, A>, signature: &TCallableSignature, name: Word) -> Option<usize>
where
    A: Arena,
{
    let parameters = signature.get_parameters();
    let named_offset = parameters.iter().position(|parameter| {
        parameter
            .get_name()
            .is_some_and(|parameter_name| parameter_name.0.as_bytes().strip_prefix(b"$") == Some(name.as_bytes()))
    });

    named_offset.or_else(|| {
        let collects_names = parameters.last().is_some_and(TCallableParameter::is_variadic)
            && !get_callback_metadata(context, signature).is_some_and(|metadata| metadata.flags.is_built_in());

        collects_names.then(|| parameters.len() - 1)
    })
}

fn report_unknown_parameter<A>(
    context: &mut Context<'_, '_, A>,
    call: &ForwardedCall<'_>,
    value: &Expression<'_>,
    name: Word,
) where
    A: Arena,
{
    let function_name = call.function_name;

    context.collector.report_with_code(
        IssueCode::InvalidNamedArgument,
        Issue::error(format!("Unknown named argument `${name}` passed on by `{function_name}()` to its callback."))
            .with_annotation(Annotation::primary(value.span()).with_message(format!("Passed as `${name}`")))
            .with_annotation(Annotation::secondary(call.callback.span()).with_message("For this callback"))
            .with_note("PHP throws an `Error` for a named argument the callback has no parameter for.")
            .with_help("Use one of the callback's parameter names."),
    );
}

fn report_named_argument_not_allowed<A>(
    context: &mut Context<'_, '_, A>,
    call: &ForwardedCall<'_>,
    value: &Expression<'_>,
    name: Word,
) where
    A: Arena,
{
    let function_name = call.function_name;

    context.collector.report_with_code(
        IssueCode::NamedArgumentNotAllowed,
        Issue::error(format!(
            "Named argument `${name}` passed on by `{function_name}()` to a callback that does not allow named arguments."
        ))
        .with_annotation(Annotation::primary(value.span()).with_message(format!("Passed as `${name}`")))
        .with_annotation(
            Annotation::secondary(call.callback.span()).with_message("This callback only accepts positional arguments"),
        )
        .with_help("Pass the value positionally."),
    );
}

fn report_missing_parameters<A>(
    context: &mut Context<'_, '_, A>,
    parameters: &[TCallableParameter],
    passed_parameters: &[bool],
    function_call: &FunctionCall<'_>,
    call: &ForwardedCall<'_>,
) where
    A: Arena,
{
    let function_name = call.function_name;

    for (parameter, _) in parameters
        .iter()
        .zip(passed_parameters)
        .filter(|(parameter, passed)| !**passed && !parameter.has_default() && !parameter.is_variadic())
    {
        let parameter_name = parameter.get_name().map_or_else(|| "?".to_string(), |name| name.0.to_string());

        context.collector.report_with_code(
            IssueCode::TooFewArguments,
            Issue::error(format!(
                "Missing parameter `{parameter_name}` in the arguments `{function_name}()` passes on to its callback."
            ))
            .with_annotation(
                Annotation::primary(function_call.argument_list.span()).with_message("No argument for it here"),
            )
            .with_annotation(Annotation::secondary(call.callback.span()).with_message("For this callback"))
            .with_help(format!("Pass an argument for `{parameter_name}`.")),
        );
    }
}

fn get_callback_metadata<'ctx, A>(
    context: &Context<'ctx, '_, A>,
    signature: &TCallableSignature,
) -> Option<&'ctx FunctionLikeMetadata>
where
    A: Arena,
{
    match signature.get_source() {
        Some(FunctionLikeIdentifier::Method(class_name, method_name)) => {
            context.codebase.get_declaring_method_by_id(&MethodIdentifier::new(class_name, method_name))
        }
        Some(identifier) => context.codebase.get_function_like(&identifier),
        None => None,
    }
}

fn get_forwarded_call<'arena, A>(
    context: &Context<'_, '_, A>,
    function_call: &FunctionCall<'arena>,
) -> Option<ForwardedCall<'arena>>
where
    A: Arena,
{
    let mut callback = None;
    let mut passed_arguments = Vec::new();

    for (offset, argument) in function_call.argument_list.arguments.iter().enumerate() {
        if callback.is_none() && (is_named(argument, b"callback") || (offset == 0 && is_plain_positional(argument))) {
            callback = Some(argument.value());
        } else {
            passed_arguments.push((offset, argument));
        }
    }

    let callback = callback?;

    if is_function(context, function_call, "call_user_func") {
        return Some(ForwardedCall {
            function_name: "call_user_func",
            callback,
            arguments: passed_arguments
                .into_iter()
                .map(|(_, argument)| forward_argument(argument))
                .collect::<Option<_>>()?,
        });
    }

    let arguments_array = passed_arguments
        .into_iter()
        .find(|(offset, argument)| is_named(argument, b"args") || (*offset == 1 && is_plain_positional(argument)))?
        .1;

    if is_function(context, function_call, "call_user_func_array") {
        return Some(ForwardedCall {
            function_name: "call_user_func_array",
            callback,
            arguments: get_array_items(arguments_array.value())?,
        });
    }

    None
}

fn forward_argument<'arena>(argument: &Argument<'arena>) -> Option<ForwardedArgument<'arena>> {
    match argument {
        Argument::Positional(positional) if positional.ellipsis.is_none() => {
            Some(ForwardedArgument { expression: positional.value, name: None })
        }
        Argument::Named(named) => {
            Some(ForwardedArgument { expression: named.value, name: Some(word(named.name.value)) })
        }
        Argument::Positional(_) => None,
    }
}

fn get_array_items<'arena>(expression: &'arena Expression<'arena>) -> Option<Vec<ForwardedArgument<'arena>>> {
    let elements = match expression {
        Expression::Array(array) => &array.elements,
        Expression::LegacyArray(array) => &array.elements,
        _ => return None,
    };

    elements
        .iter()
        .map(|element| match element {
            ArrayElement::Value(element) => Some(ForwardedArgument { expression: element.value, name: None }),
            ArrayElement::KeyValue(element) => match element.key {
                Expression::Literal(Literal::Integer(_)) => {
                    Some(ForwardedArgument { expression: element.value, name: None })
                }
                Expression::Literal(Literal::String(key)) => match ArrayKey::from_string(word(key.value?)) {
                    ArrayKey::Integer(_) => Some(ForwardedArgument { expression: element.value, name: None }),
                    ArrayKey::String(name) if !name.is_empty() => {
                        Some(ForwardedArgument { expression: element.value, name: Some(name) })
                    }
                    _ => None,
                },
                _ => None,
            },
            ArrayElement::Variadic(_) | ArrayElement::Missing(_) => None,
        })
        .collect()
}

fn get_callback_signature<A>(context: &Context<'_, '_, A>, callback: &TAtomic) -> Option<TCallableSignature>
where
    A: Arena,
{
    match cast_atomic_to_callable(callback, context.codebase, None)?.as_ref() {
        TCallable::Signature(signature) => Some(signature.clone()),
        TCallable::Alias(identifier) => get_signature_of_function_like_identifier(identifier, context.codebase),
    }
}

fn is_function<A>(context: &Context<'_, '_, A>, function_call: &FunctionCall<'_>, name: &str) -> bool
where
    A: Arena,
{
    let Expression::Identifier(identifier) = function_call.function else {
        return false;
    };

    (identifier.is_local() && ascii_lowercase_word(identifier.value()).as_bytes() == name.as_bytes())
        || ascii_lowercase_word(context.resolved_names.get(identifier)).as_bytes() == name.as_bytes()
}

fn is_named(argument: &Argument<'_>, name: &[u8]) -> bool {
    matches!(argument, Argument::Named(named) if named.name.value == name)
}

const fn is_plain_positional(argument: &Argument<'_>) -> bool {
    matches!(argument, Argument::Positional(positional) if positional.ellipsis.is_none())
}
