use mago_allocator::Arena;
use mago_codex::identifier::function_like::FunctionLikeIdentifier;
use mago_codex::identifier::method::MethodIdentifier;
use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::atomic::callable::TCallable;
use mago_codex::ttype::atomic::callable::TCallableSignature;
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
use mago_word::ascii_lowercase_word;

use crate::artifacts::AnalysisArtifacts;
use crate::code::IssueCode;
use crate::context::Context;
use crate::invocation::InvocationTarget;
use crate::invocation::arguments::verify_argument_type;

struct ForwardedCall<'arena> {
    function_name: &'static str,
    callback: &'arena Expression<'arena>,
    arguments: Vec<&'arena Expression<'arena>>,
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

    report_argument_count(context, &signature, function_call, &call);

    let target = InvocationTarget::Callable {
        source: signature.get_source(),
        signature: signature.clone(),
        effective_signature: None,
        span: call.callback.span(),
    };
    let parameters = signature.get_parameters();

    for (offset, argument) in call.arguments.iter().copied().enumerate() {
        let Some(parameter) = parameters.get(offset).or_else(|| parameters.last().filter(|last| last.is_variadic()))
        else {
            return;
        };

        let (Some(parameter_type), Some(argument_type)) =
            (parameter.get_type_signature(), artifacts.get_expression_type(argument))
        else {
            continue;
        };

        let parameter_type = replace_template_parameters_with_constraints(parameter_type, context.codebase);
        verify_argument_type(context, argument_type, &parameter_type, offset, argument, &target);
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
    let parameters = signature.get_parameters();
    let required_count =
        parameters.iter().filter(|parameter| !parameter.has_default() && !parameter.is_variadic()).count();
    let passed_count = call.arguments.len();
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
        || callback_reads_extra_arguments(context, signature);
    let Some(first_extra_argument) = call.arguments.get(parameters.len()).filter(|_| !takes_any_count) else {
        return;
    };

    context.collector.report_with_code(
        IssueCode::TooManyArguments,
        Issue::error(format!("Too many arguments passed on by `{function_name}()` to its callback."))
            .with_annotation(
                Annotation::primary(first_extra_argument.span()).with_message("Unexpected argument provided here"),
            )
            .with_annotation(Annotation::secondary(call.callback.span()).with_message("For this callback"))
            .with_note(format!(
                "The callback takes {} argument(s), but `{function_name}()` passes on {passed_count}.",
                parameters.len()
            ))
            .with_help("Remove the extra argument(s)."),
    );
}

fn callback_reads_extra_arguments<A>(context: &Context<'_, '_, A>, signature: &TCallableSignature) -> bool
where
    A: Arena,
{
    let metadata = match signature.get_source() {
        Some(FunctionLikeIdentifier::Method(class_name, method_name)) => {
            context.codebase.get_declaring_method_by_id(&MethodIdentifier::new(class_name, method_name))
        }
        Some(identifier) => context.codebase.get_function_like(&identifier),
        None => None,
    };

    metadata.is_some_and(|metadata| metadata.flags.uses_func_get_args())
}

fn get_forwarded_call<'arena, A>(
    context: &Context<'_, '_, A>,
    function_call: &FunctionCall<'arena>,
) -> Option<ForwardedCall<'arena>>
where
    A: Arena,
{
    let arguments = function_call.argument_list.arguments.as_slice();
    if !arguments.iter().all(is_plain_positional) {
        return None;
    }

    let (callback, passed_arguments) = arguments.split_first()?;

    if is_function(context, function_call, "call_user_func") {
        return Some(ForwardedCall {
            function_name: "call_user_func",
            callback: callback.value(),
            arguments: passed_arguments.iter().map(Argument::value).collect(),
        });
    }

    if let [arguments_array] = passed_arguments
        && is_function(context, function_call, "call_user_func_array")
    {
        return Some(ForwardedCall {
            function_name: "call_user_func_array",
            callback: callback.value(),
            arguments: get_positional_array_values(arguments_array.value())?,
        });
    }

    None
}

fn get_positional_array_values<'arena>(
    expression: &'arena Expression<'arena>,
) -> Option<Vec<&'arena Expression<'arena>>> {
    let elements = match expression {
        Expression::Array(array) => &array.elements,
        Expression::LegacyArray(array) => &array.elements,
        _ => return None,
    };

    elements
        .iter()
        .map(|element| match element {
            ArrayElement::Value(element) => Some(element.value),
            ArrayElement::KeyValue(element) if matches!(element.key, Expression::Literal(Literal::Integer(_))) => {
                Some(element.value)
            }
            _ => None,
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

const fn is_plain_positional(argument: &Argument<'_>) -> bool {
    matches!(argument, Argument::Positional(positional) if positional.ellipsis.is_none())
}
