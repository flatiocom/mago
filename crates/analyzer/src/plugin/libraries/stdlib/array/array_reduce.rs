use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::atomic::array::TArray;
use mago_codex::ttype::get_null;
use mago_codex::ttype::union::TUnion;

use crate::plugin::context::InvocationInfo;
use crate::plugin::context::ProviderContext;
use crate::plugin::provider::Provider;
use crate::plugin::provider::ProviderMeta;
use crate::plugin::provider::function::FunctionReturnTypeProvider;
use crate::plugin::provider::function::FunctionTarget;

static META: ProviderMeta = ProviderMeta::new(
    "php::array::array_reduce",
    "array_reduce",
    "Returns what the callback returns when the array is non-empty",
);

#[derive(Default)]
pub struct ArrayReduceProvider;

impl Provider for ArrayReduceProvider {
    fn meta() -> &'static ProviderMeta {
        &META
    }
}

impl FunctionReturnTypeProvider for ArrayReduceProvider {
    fn targets() -> FunctionTarget {
        FunctionTarget::Exact(b"array_reduce")
    }

    fn get_return_type(
        &self,
        context: &ProviderContext<'_, '_, '_>,
        invocation: &InvocationInfo<'_, '_, '_>,
    ) -> Option<TUnion> {
        let array_type = context.get_expression_type(invocation.get_argument(0, &[b"array"])?)?;
        let arrays = array_type
            .types
            .iter()
            .map(|atomic| if let TAtomic::Array(array) = atomic { Some(array) } else { None })
            .collect::<Option<Vec<_>>>()?;
        if !arrays.iter().all(|array| array.is_sealed()) {
            return None;
        }

        if arrays.iter().all(|array| array.is_empty()) {
            return Some(
                invocation
                    .get_argument(2, &[b"initial"])
                    .and_then(|initial| context.get_expression_type(initial))
                    .cloned()
                    .unwrap_or_else(get_null),
            );
        }

        if !arrays.iter().all(|array| is_iterated_at_least_once(array)) {
            return None;
        }

        let callback_argument = invocation.get_argument(1, &[b"callback"])?;
        let callback_type = context.get_expression_type(callback_argument)?;
        let inferred_return_type = if callback_type.is_single()
            && let TAtomic::Callable(callable) = callback_type.get_single()
        {
            callable.get_signature().and_then(|signature| signature.get_return_type())
        } else {
            None
        };
        let declared_return_type = context
            .get_callable_metadata(callback_argument)
            .and_then(|metadata| metadata.return_type_metadata.as_ref())
            .map(|return_type| &return_type.type_union);

        inferred_return_type.or(declared_return_type).cloned()
    }
}

fn is_iterated_at_least_once(array: &TArray) -> bool {
    array.is_non_empty()
        || match array {
            TArray::Keyed(keyed) => {
                keyed.known_items.as_deref().is_some_and(|items| items.values().any(|(optional, _)| !optional))
            }
            TArray::List(list) => {
                list.known_elements.as_deref().is_some_and(|elements| elements.values().any(|(optional, _)| !optional))
            }
        }
}
