use std::sync::Arc;

use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::atomic::array::TArray;
use mago_codex::ttype::atomic::scalar::TScalar;
use mago_codex::ttype::atomic::scalar::bool::TBool;
use mago_codex::ttype::combiner;
use mago_codex::ttype::combiner::CombinerOptions;
use mago_codex::ttype::union::TUnion;

use crate::plugin::context::InvocationInfo;
use crate::plugin::context::ProviderContext;
use crate::plugin::provider::Provider;
use crate::plugin::provider::ProviderMeta;
use crate::plugin::provider::function::FunctionReturnTypeProvider;
use crate::plugin::provider::function::FunctionTarget;

static META: ProviderMeta = ProviderMeta::new(
    "php::string::mb_convert_encoding",
    "mb_convert_encoding",
    "Returns the shape of the input with string values, false only when the source encoding is detected, like PHPStan",
);

#[derive(Default)]
pub struct MbConvertEncodingProvider;

impl Provider for MbConvertEncodingProvider {
    fn meta() -> &'static ProviderMeta {
        &META
    }
}

impl FunctionReturnTypeProvider for MbConvertEncodingProvider {
    fn targets() -> FunctionTarget {
        FunctionTarget::Exact(b"mb_convert_encoding")
    }

    fn get_return_type(
        &self,
        context: &ProviderContext<'_, '_, '_>,
        invocation: &InvocationInfo<'_, '_, '_>,
    ) -> Option<TUnion> {
        let value_argument = invocation.get_argument(0, &[b"string"])?;
        let value_type = context.get_expression_type(value_argument)?;

        let mut converted = value_type.types.iter().map(with_string_values).collect::<Option<Vec<_>>>()?;

        let detects_encoding = invocation
            .get_argument(2, &[b"from_encoding"])
            .and_then(|argument| context.get_expression_type(argument))
            .is_some_and(may_detect_encoding);
        if detects_encoding {
            converted.push(TAtomic::Scalar(TScalar::Bool(TBool::r#false())));
        }

        Some(TUnion::from_vec(combiner::combine(converted, context.codebase(), CombinerOptions::default())))
    }
}

fn with_string_values(atomic: &TAtomic) -> Option<TAtomic> {
    match atomic {
        TAtomic::Scalar(TScalar::String(_) | TScalar::ClassLikeString(_)) => Some(TAtomic::Scalar(TScalar::string())),
        TAtomic::Array(TArray::List(list)) => {
            let mut list = list.clone();
            list.element_type = Arc::new(map_string_values(&list.element_type));
            if let Some(known_elements) = &list.known_elements {
                list.known_elements = Some(Arc::new(
                    known_elements
                        .iter()
                        .map(|(index, (optional, element))| (*index, (*optional, map_string_values(element))))
                        .collect(),
                ));
            }

            Some(TAtomic::Array(TArray::List(list)))
        }
        TAtomic::Array(TArray::Keyed(keyed_array)) => {
            let mut keyed_array = keyed_array.clone();
            if let Some((key, value)) = &keyed_array.parameters {
                keyed_array.parameters = Some((Arc::clone(key), Arc::new(map_string_values(value))));
            }

            if let Some(known_items) = &keyed_array.known_items {
                keyed_array.known_items = Some(Arc::new(
                    known_items
                        .iter()
                        .map(|(key, (optional, item))| (*key, (*optional, map_string_values(item))))
                        .collect(),
                ));
            }

            Some(TAtomic::Array(TArray::Keyed(keyed_array)))
        }
        _ => None,
    }
}

fn map_string_values(union: &TUnion) -> TUnion {
    TUnion::from_vec(
        union.types.iter().map(|atomic| with_string_values(atomic).unwrap_or_else(|| atomic.clone())).collect(),
    )
}

fn may_detect_encoding(from_encoding: &TUnion) -> bool {
    from_encoding.types.iter().any(|atomic| match atomic {
        TAtomic::Scalar(TScalar::String(_)) => atomic.get_literal_string_value().is_none_or(names_several_encodings),
        TAtomic::Array(array) => !array.is_sealed() || array_size(array) > 1,
        TAtomic::Mixed(_) => true,
        _ => false,
    })
}

fn names_several_encodings(encoding: &[u8]) -> bool {
    encoding.contains(&b',') || encoding.trim_ascii().eq_ignore_ascii_case(b"auto")
}

fn array_size(array: &TArray) -> usize {
    match array {
        TArray::List(list) => list.known_elements.as_ref().map_or(0, |elements| elements.len()),
        TArray::Keyed(keyed_array) => keyed_array.known_items.as_ref().map_or(0, |items| items.len()),
    }
}
