use mago_allocator::Arena;
use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::atomic::callable::TCallable;
use mago_codex::ttype::atomic::callable::TCallableSignature;
use mago_codex::ttype::cast::cast_atomic_to_callable;
use mago_codex::ttype::expander::get_signature_of_function_like_identifier;
use mago_codex::ttype::template::inferred_type_replacer::replace_template_parameters_with_constraints;
use mago_span::HasSpan;
use mago_syntax::cst::Argument;
use mago_syntax::cst::Expression;
use mago_syntax::cst::FunctionCall;
use mago_word::ascii_lowercase_word;

use crate::artifacts::AnalysisArtifacts;
use crate::context::Context;
use crate::invocation::InvocationTarget;
use crate::invocation::arguments::verify_argument_type;

pub(super) fn check_call_user_func_arguments<'arena, A>(
    context: &mut Context<'_, 'arena, A>,
    artifacts: &AnalysisArtifacts,
    function_call: &FunctionCall<'arena>,
) where
    A: Arena,
{
    if !is_call_user_func(context, function_call) {
        return;
    }

    let arguments = function_call.argument_list.arguments.as_slice();
    let Some((callback, passed_arguments)) = arguments.split_first() else {
        return;
    };

    if !arguments.iter().all(is_plain_positional) {
        return;
    }

    let Some(signature) =
        artifacts.get_expression_type(callback.value()).and_then(|callback_type| match callback_type.types.as_ref() {
            [callback_atomic] => get_callback_signature(context, callback_atomic),
            _ => None,
        })
    else {
        return;
    };

    if signature.has_no_signature() {
        return;
    }

    let target = InvocationTarget::Callable {
        source: signature.get_source(),
        signature: signature.clone(),
        effective_signature: None,
        span: callback.span(),
    };
    let parameters = signature.get_parameters();

    for (offset, argument) in passed_arguments.iter().enumerate() {
        let Some(parameter) = parameters.get(offset).or_else(|| parameters.last().filter(|last| last.is_variadic()))
        else {
            return;
        };

        let (Some(parameter_type), Some(argument_type)) =
            (parameter.get_type_signature(), artifacts.get_expression_type(argument.value()))
        else {
            continue;
        };

        let parameter_type = replace_template_parameters_with_constraints(parameter_type, context.codebase);
        verify_argument_type(context, argument_type, &parameter_type, offset, argument.value(), &target);
    }
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

fn is_call_user_func<A>(context: &Context<'_, '_, A>, function_call: &FunctionCall<'_>) -> bool
where
    A: Arena,
{
    let Expression::Identifier(identifier) = function_call.function else {
        return false;
    };

    (identifier.is_local() && ascii_lowercase_word(identifier.value()).as_bytes() == b"call_user_func")
        || ascii_lowercase_word(context.resolved_names.get(identifier)).as_bytes() == b"call_user_func"
}

const fn is_plain_positional(argument: &Argument<'_>) -> bool {
    matches!(argument, Argument::Positional(positional) if positional.ellipsis.is_none())
}
