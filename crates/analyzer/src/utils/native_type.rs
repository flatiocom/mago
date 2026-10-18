use mago_allocator::Arena;

use mago_codex::metadata::function_like::FunctionLikeMetadata;
use mago_codex::ttype::add_optional_union_type;
use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::atomic::object::TObject;
use mago_codex::ttype::atomic::object::named::TNamedObject;
use mago_codex::ttype::expander;
use mago_codex::ttype::expander::StaticClassType;
use mago_codex::ttype::expander::TypeExpansionOptions;
use mago_codex::ttype::get_null;
use mago_codex::ttype::union::TUnion;
use mago_syntax::cst::Call;
use mago_syntax::cst::ClassLikeMemberSelector;
use mago_syntax::cst::Expression;
use mago_word::Word;
use mago_word::word;

use crate::artifacts::AnalysisArtifacts;
use crate::context::Context;
use crate::context::block::BlockContext;

pub fn get_native_expression_type<A>(
    context: &Context<'_, '_, A>,
    block_context: &BlockContext<'_>,
    artifacts: &AnalysisArtifacts,
    expression: &Expression<'_>,
) -> Option<TUnion>
where
    A: Arena,
{
    match expression.unparenthesized() {
        Expression::Instantiation(instantiation) => {
            let class_name = resolve_class_name(context, block_context, instantiation.class)?;

            Some(TUnion::from_atomic(TAtomic::Object(TObject::Named(TNamedObject::new(class_name)))))
        }
        Expression::Call(Call::Function(call)) => {
            let Expression::Identifier(identifier) = call.function else {
                return None;
            };

            let function = context
                .codebase
                .get_function(context.resolved_names.get(identifier))
                .or_else(|| context.codebase.get_function(identifier.value()))?;

            get_declared_return_type(context, function, None, None)
        }
        Expression::Call(Call::Method(call)) => {
            get_method_return_type(context, artifacts, call.object, &call.method, false)
        }
        Expression::Call(Call::NullSafeMethod(call)) => {
            get_method_return_type(context, artifacts, call.object, &call.method, true)
        }
        Expression::Call(Call::StaticMethod(call)) => {
            let class_name = resolve_class_name(context, block_context, call.class)?;

            get_class_method_return_type(context, class_name, &call.method)
        }
        _ => None,
    }
}

fn get_method_return_type<A>(
    context: &Context<'_, '_, A>,
    artifacts: &AnalysisArtifacts,
    object: &Expression<'_>,
    method: &ClassLikeMemberSelector<'_>,
    is_nullsafe: bool,
) -> Option<TUnion>
where
    A: Arena,
{
    let object_type = artifacts.get_expression_type(object)?;

    let mut return_type = None;
    for atomic in object_type.types.iter() {
        let member_type = match atomic {
            TAtomic::Object(TObject::Named(named_object)) => {
                get_class_method_return_type(context, named_object.name, method)?
            }
            TAtomic::Null if is_nullsafe => get_null(),
            _ => return None,
        };

        return_type = Some(add_optional_union_type(member_type, return_type.as_ref(), context.codebase));
    }

    return_type
}

fn get_class_method_return_type<A>(
    context: &Context<'_, '_, A>,
    class_name: Word,
    method: &ClassLikeMemberSelector<'_>,
) -> Option<TUnion>
where
    A: Arena,
{
    let ClassLikeMemberSelector::Identifier(method_name) = method else {
        return None;
    };

    let declaring_class = context.codebase.get_declaring_method_class(class_name.as_bytes(), method_name.value)?;
    let method = context.codebase.get_declaring_method(class_name.as_bytes(), method_name.value)?;

    get_declared_return_type(context, method, Some(declaring_class), Some(class_name))
}

fn get_declared_return_type<A>(
    context: &Context<'_, '_, A>,
    function_like: &FunctionLikeMetadata,
    self_class: Option<Word>,
    static_class: Option<Word>,
) -> Option<TUnion>
where
    A: Arena,
{
    let mut return_type = function_like.return_type_declaration_metadata.as_ref()?.type_union.clone();

    expander::expand_union(
        context.codebase,
        &mut return_type,
        &TypeExpansionOptions {
            self_class,
            static_class_type: static_class.map_or(StaticClassType::None, StaticClassType::Exact),
            ..Default::default()
        },
    );

    if return_type.is_mixed() || return_type.has_template_types() {
        return None;
    }

    Some(return_type)
}

fn resolve_class_name<A>(
    context: &Context<'_, '_, A>,
    block_context: &BlockContext<'_>,
    class: &Expression<'_>,
) -> Option<Word>
where
    A: Arena,
{
    match class.unparenthesized() {
        Expression::Identifier(identifier) => Some(word(context.resolved_names.get(identifier))),
        Expression::Self_(_) | Expression::Static(_) => block_context.scope.get_class_like_name(),
        Expression::Parent(_) => block_context.scope.get_class_like()?.direct_parent_class,
        _ => None,
    }
}
