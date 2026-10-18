use mago_names::ResolvedNames;
use mago_syntax::cst::Access;
use mago_syntax::cst::ArrayElement;
use mago_syntax::cst::Block;
use mago_syntax::cst::Call;
use mago_syntax::cst::ClassLikeMemberSelector;
use mago_syntax::cst::Expression;
use mago_syntax::cst::FunctionCall;
use mago_syntax::cst::MethodCall;
use mago_syntax::cst::Node;
use mago_syntax::cst::NullSafeMethodCall;
use mago_syntax::cst::StaticMethodCall;
use mago_syntax::cst::UnaryPrefixOperator;
use mago_syntax::cst::Variable;
use mago_word::ascii_lowercase_word;
use mago_word::word;

use crate::metadata::class_like::ClassLikeMetadata;
use crate::metadata::function_like::EffectDependency;

#[must_use]
pub fn is_effectful_construct(node: &Node<'_, '_>) -> bool {
    match node {
        Node::Closure(_)
        | Node::ArrowFunction(_)
        | Node::AnonymousClass(_)
        | Node::Function(_)
        | Node::Class(_)
        | Node::Interface(_)
        | Node::Trait(_)
        | Node::Enum(_)
        | Node::Echo(_)
        | Node::EchoTag(_)
        | Node::Inline(_)
        | Node::Global(_)
        | Node::Static(_)
        | Node::Unset(_)
        | Node::PrintConstruct(_)
        | Node::ExitConstruct(_)
        | Node::DieConstruct(_)
        | Node::EvalConstruct(_)
        | Node::IncludeConstruct(_)
        | Node::IncludeOnceConstruct(_)
        | Node::RequireConstruct(_)
        | Node::RequireOnceConstruct(_)
        | Node::Yield(_)
        | Node::Throw(_)
        | Node::StaticPropertyAccess(_)
        | Node::Pipe(_) => true,
        Node::DirectVariable(variable) => is_superglobal(variable.name),
        _ => false,
    }
}

pub(crate) fn scan_constructor_effect_dependencies(
    body: &Block<'_>,
    class: &ClassLikeMetadata,
    resolved_names: &ResolvedNames<'_>,
) -> Option<Vec<EffectDependency>> {
    let mut dependencies = vec![];
    let effect_free = body
        .statements
        .iter()
        .all(|statement| collect_dependencies(Node::Statement(statement), class, resolved_names, &mut dependencies));

    effect_free.then_some(dependencies)
}

fn collect_dependencies(
    node: Node<'_, '_>,
    class: &ClassLikeMetadata,
    resolved_names: &ResolvedNames<'_>,
    dependencies: &mut Vec<EffectDependency>,
) -> bool {
    if is_effectful_construct(&node) {
        return false;
    }

    let effect_free = match node {
        Node::Call(call) => match call_dependency(call, class, resolved_names) {
            Some(dependency) => {
                dependencies.push(dependency);
                true
            }
            None => false,
        },
        Node::Instantiation(instantiation) => match instantiation.class {
            Expression::Identifier(class_name) => {
                dependencies.push(EffectDependency::Constructor(ascii_lowercase_word(resolved_names.get(class_name))));
                true
            }
            _ => false,
        },
        Node::Assignment(assignment) => {
            !matches!(assignment.rhs, Expression::UnaryPrefix(prefix) if matches!(prefix.operator, UnaryPrefixOperator::Reference(_)))
                && writes_only_locals_or_this(assignment.lhs)
        }
        Node::UnaryPrefix(prefix) if prefix.operator.is_increment_or_decrement() => {
            writes_only_locals_or_this(prefix.operand)
        }
        Node::UnaryPostfix(postfix) => writes_only_locals_or_this(postfix.operand),
        _ => true,
    };

    effect_free
        && node.children().into_iter().all(|child| collect_dependencies(child, class, resolved_names, dependencies))
}

fn call_dependency(
    call: &Call<'_>,
    class: &ClassLikeMetadata,
    resolved_names: &ResolvedNames<'_>,
) -> Option<EffectDependency> {
    match call {
        Call::Function(FunctionCall { function: Expression::Identifier(function_name), .. }) => {
            Some(EffectDependency::Function {
                name: word(resolved_names.get(function_name)),
                global_fallback: function_name.is_local().then(|| word(function_name.value())),
            })
        }
        Call::Method(MethodCall { object, method: ClassLikeMemberSelector::Identifier(method), .. })
        | Call::NullSafeMethod(NullSafeMethodCall {
            object,
            method: ClassLikeMemberSelector::Identifier(method),
            ..
        }) if is_this(object) => {
            Some(EffectDependency::Method { class: class.name, method: ascii_lowercase_word(method.value) })
        }
        Call::StaticMethod(StaticMethodCall {
            class: called_class,
            method: ClassLikeMemberSelector::Identifier(method),
            ..
        }) => {
            let called_class = match called_class {
                Expression::Self_(_) | Expression::Static(_) => class.name,
                Expression::Parent(_) => class.direct_parent_class?,
                Expression::Identifier(class_name) => ascii_lowercase_word(resolved_names.get(class_name)),
                _ => return None,
            };

            Some(EffectDependency::Method { class: called_class, method: ascii_lowercase_word(method.value) })
        }
        _ => None,
    }
}

fn writes_only_locals_or_this(target: &Expression<'_>) -> bool {
    match target {
        Expression::Variable(_) => true,
        Expression::Parenthesized(parenthesized) => writes_only_locals_or_this(parenthesized.expression),
        Expression::ArrayAccess(access) => writes_only_locals_or_this(access.array),
        Expression::ArrayAppend(append) => writes_only_locals_or_this(append.array),
        Expression::Access(Access::Property(access)) => is_this(access.object),
        Expression::Array(array) => array.elements.iter().all(element_writes_only_locals_or_this),
        Expression::LegacyArray(array) => array.elements.iter().all(element_writes_only_locals_or_this),
        Expression::List(list) => list.elements.iter().all(element_writes_only_locals_or_this),
        _ => false,
    }
}

fn element_writes_only_locals_or_this(element: &ArrayElement<'_>) -> bool {
    match element {
        ArrayElement::KeyValue(element) => writes_only_locals_or_this(element.value),
        ArrayElement::Value(element) => writes_only_locals_or_this(element.value),
        ArrayElement::Missing(_) => true,
        ArrayElement::Variadic(_) => false,
    }
}

fn is_this(expression: &Expression<'_>) -> bool {
    matches!(expression, Expression::Variable(Variable::Direct(variable)) if variable.name == b"$this")
}

fn is_superglobal(name: &[u8]) -> bool {
    matches!(
        name,
        b"$GLOBALS"
            | b"$_SERVER"
            | b"$_GET"
            | b"$_POST"
            | b"$_FILES"
            | b"$_COOKIE"
            | b"$_SESSION"
            | b"$_REQUEST"
            | b"$_ENV"
    )
}
