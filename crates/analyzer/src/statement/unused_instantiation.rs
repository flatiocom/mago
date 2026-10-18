use mago_allocator::Arena;
use mago_codex::metadata::CodebaseMetadata;
use mago_codex::metadata::class_like::ClassLikeMetadata;
use mago_codex::metadata::function_like::EffectDependency;
use mago_codex::metadata::function_like::FunctionLikeMetadata;
use mago_codex::scanner::effect::is_effectful_construct;
use mago_syntax::cst::Call;
use mago_syntax::cst::Expression;
use mago_syntax::cst::FunctionCall;
use mago_syntax::cst::Instantiation;
use mago_syntax::cst::Node;
use mago_word::Word;
use mago_word::word;

use crate::artifacts::AnalysisArtifacts;
use crate::context::Context;
use crate::context::block::BlockContext;
use crate::utils::expression::get_function_like_id_from_call;

pub(super) fn instantiation_has_no_effect<'arena, A>(
    instantiation: &Instantiation<'arena>,
    context: &Context<'_, 'arena, A>,
    block_context: &BlockContext<'_>,
    artifacts: &AnalysisArtifacts,
) -> bool
where
    A: Arena,
{
    let Some(class) = instantiated_class(instantiation.class, context, block_context) else {
        return false;
    };

    if !class.kind.is_class() || class.flags.is_abstract() {
        return false;
    }

    match context.codebase.get_declaring_method(class.name.as_bytes(), b"__construct") {
        None => arguments_have_no_effect(instantiation, context, block_context, artifacts),
        Some(constructor) if constructor.flags.is_pure() => {
            constructor.assertions.is_empty()
                && constructor.thrown_types.is_empty()
                && arguments_have_no_effect(instantiation, context, block_context, artifacts)
        }
        Some(_) => {
            matches!(instantiation.class, Expression::Identifier(_))
                && constructor_is_effect_free(context.codebase, class.name, &mut vec![])
        }
    }
}

fn instantiated_class<'ctx, 'arena, A>(
    class: &Expression<'arena>,
    context: &Context<'ctx, 'arena, A>,
    block_context: &BlockContext<'_>,
) -> Option<&'ctx ClassLikeMetadata>
where
    A: Arena,
{
    let class_name = match class {
        Expression::Identifier(identifier) => word(context.resolved_names.get(identifier)),
        Expression::Self_(_) | Expression::Static(_) => block_context.scope.get_class_like_name()?,
        Expression::Parent(_) => block_context.scope.get_class_like()?.direct_parent_class?,
        _ => return None,
    };

    context.codebase.get_class_like(class_name.as_bytes())
}

fn arguments_have_no_effect<'arena, A>(
    instantiation: &Instantiation<'arena>,
    context: &Context<'_, 'arena, A>,
    block_context: &BlockContext<'_>,
    artifacts: &AnalysisArtifacts,
) -> bool
where
    A: Arena,
{
    instantiation
        .argument_list
        .iter()
        .all(|arguments| !has_effect(Node::ArgumentList(arguments), context, block_context, artifacts))
}

fn has_effect<'arena, A>(
    node: Node<'_, 'arena>,
    context: &Context<'_, 'arena, A>,
    block_context: &BlockContext<'_>,
    artifacts: &AnalysisArtifacts,
) -> bool
where
    A: Arena,
{
    if is_effectful_construct(&node) {
        return true;
    }

    let effectful = match node {
        Node::Call(call) => !call_target(call, context, artifacts).is_some_and(is_pure_without_throws),
        Node::Instantiation(instantiation) => !instantiated_class(instantiation.class, context, block_context)
            .is_some_and(|class| {
                class.kind.is_class()
                    && context
                        .codebase
                        .get_declaring_method(class.name.as_bytes(), b"__construct")
                        .is_none_or(|constructor| constructor.flags.is_pure() && constructor.thrown_types.is_empty())
            }),
        _ => false,
    };

    effectful || node.children().into_iter().any(|child| has_effect(child, context, block_context, artifacts))
}

fn call_target<'ctx, 'arena, A>(
    call: &Call<'arena>,
    context: &Context<'ctx, 'arena, A>,
    artifacts: &AnalysisArtifacts,
) -> Option<&'ctx FunctionLikeMetadata>
where
    A: Arena,
{
    if let Call::Function(FunctionCall { function: Expression::Identifier(function_name), .. }) = call {
        let global_fallback = function_name.is_local().then(|| word(function_name.value()));

        return find_function(context.codebase, word(context.resolved_names.get(function_name)), global_fallback);
    }

    let identifier = get_function_like_id_from_call(call, context.resolved_names, &artifacts.expression_types)?;

    context.codebase.get_function_like(&identifier)
}

fn find_function(
    codebase: &CodebaseMetadata,
    name: Word,
    global_fallback: Option<Word>,
) -> Option<&FunctionLikeMetadata> {
    codebase.get_function(name.as_bytes()).or_else(|| codebase.get_function(global_fallback?.as_bytes()))
}

fn is_pure_without_throws(function_like: &FunctionLikeMetadata) -> bool {
    function_like.flags.is_pure() && function_like.thrown_types.is_empty() && !takes_reference(function_like)
}

fn takes_reference(function_like: &FunctionLikeMetadata) -> bool {
    function_like.parameters.iter().any(|parameter| parameter.flags.is_by_reference())
}

fn constructor_is_effect_free(codebase: &CodebaseMetadata, class_name: Word, visiting: &mut Vec<Word>) -> bool {
    let Some(class) = codebase.get_class_like(class_name.as_bytes()) else {
        return false;
    };

    if !class.kind.is_class() {
        return false;
    }

    let Some(constructor) = codebase.get_declaring_method(class.name.as_bytes(), b"__construct") else {
        return true;
    };

    if constructor.flags.is_pure() {
        return constructor.thrown_types.is_empty();
    }

    let Some(dependencies) =
        constructor.method_metadata.as_ref().and_then(|method| method.effect_dependencies.as_ref())
    else {
        return false;
    };

    let Some(declaring_class) = codebase.get_declaring_method_class(class.name.as_bytes(), b"__construct") else {
        return false;
    };

    if !constructor.flags.is_user_defined()
        || !constructor.thrown_types.is_empty()
        || !constructor.assertions.is_empty()
        || takes_reference(constructor)
        || visiting.contains(&declaring_class)
    {
        return false;
    }

    visiting.push(declaring_class);
    let effect_free = dependencies.iter().all(|dependency| dependency_is_effect_free(codebase, dependency, visiting));
    visiting.pop();

    effect_free
}

fn dependency_is_effect_free(
    codebase: &CodebaseMetadata,
    dependency: &EffectDependency,
    visiting: &mut Vec<Word>,
) -> bool {
    match dependency {
        EffectDependency::Function { name, global_fallback } => {
            find_function(codebase, *name, *global_fallback).is_some_and(is_pure_without_throws)
        }
        EffectDependency::Method { class, method } if method.as_bytes() == b"__construct" => {
            constructor_is_effect_free(codebase, *class, visiting)
        }
        EffectDependency::Method { class, method } => {
            codebase.get_declaring_method(class.as_bytes(), method.as_bytes()).is_some_and(is_pure_without_throws)
        }
        EffectDependency::Constructor(class) => constructor_is_effect_free(codebase, *class, visiting),
    }
}
