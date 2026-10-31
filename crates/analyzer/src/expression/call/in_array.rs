use mago_allocator::Arena;
use mago_codex::metadata::CodebaseMetadata;
use mago_codex::ttype::TType;
use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::atomic::array::TArray;
use mago_codex::ttype::atomic::object::TObject;
use mago_codex::ttype::atomic::scalar::TScalar;
use mago_codex::ttype::atomic::scalar::class_like_string::TClassLikeString;
use mago_codex::ttype::atomic::scalar::int::TInteger;
use mago_codex::ttype::combine_union_types;
use mago_codex::ttype::combiner::CombinerOptions;
use mago_codex::ttype::comparator::union_comparator::can_expression_types_be_identical;
use mago_codex::ttype::get_array_value_parameter;
use mago_codex::ttype::get_never;
use mago_codex::ttype::union::TUnion;
use mago_reporting::Annotation;
use mago_reporting::Issue;
use mago_span::HasSpan;
use mago_syntax::cst::Argument;
use mago_syntax::cst::Expression;
use mago_syntax::cst::FunctionCall;
use mago_word::Word;
use mago_word::ascii_lowercase_word;

use crate::artifacts::AnalysisArtifacts;
use crate::code::IssueCode;
use crate::context::Context;

const MAX_FINITE_VALUES: usize = 128;

/// One value a type can hold, compared as `===` compares it.
#[derive(Debug, Clone, PartialEq)]
enum FiniteValue {
    String(Vec<u8>),
    Int(i64),
    Float(f64),
    Bool(bool),
    Null,
    EnumCase(Word, Word),
}

/// Reports a strict `in_array()` call that always returns `false`, its needle sharing no value with the
/// haystack, like PHPStan's `function.impossibleType`, or always `true`, its needle holding only values
/// that every possible haystack is sure to contain, like PHPStan's `function.alreadyNarrowedType`.
pub(super) fn report_constant_in_array<A>(
    context: &mut Context<'_, '_, A>,
    artifacts: &AnalysisArtifacts,
    function_call: &FunctionCall<'_>,
) where
    A: Arena,
{
    if !is_in_array(context, function_call) {
        return;
    }

    let Some((needle, haystack, strict)) = get_arguments(function_call) else {
        return;
    };

    let (Some(needle_type), Some(haystack_type)) =
        (artifacts.get_expression_type(needle), artifacts.get_expression_type(haystack))
    else {
        return;
    };

    let codebase = context.codebase;
    let needle_values = get_finite_values(codebase, needle_type);

    let is_strict = strict.and_then(|strict| artifacts.get_expression_type(strict)).is_some_and(TUnion::is_true)
        || needle_values
            .as_ref()
            .is_some_and(|values| values.iter().all(|value| matches!(value, FiniteValue::EnumCase(..))))
        || haystack_holds_only_enums(codebase, haystack_type);

    if !is_strict {
        return;
    }

    let needle_type_id = needle_type.get_id();
    if is_never_in_haystack(codebase, needle_type, haystack_type) {
        context.collector.report_with_code(
            IssueCode::ImpossibleTypeComparison,
            Issue::warning(format!(
                "Impossible `in_array()` call: the needle of type `{needle_type_id}` is never in the haystack."
            ))
            .with_annotation(
                Annotation::primary(function_call.span()).with_message("This call always returns `false`."),
            )
            .with_help("Consider removing this check, or fix the needle or the haystack."),
        );

        return;
    }

    let Some(needle_values) = needle_values else {
        return;
    };

    if context.last_conditions.contains(&function_call.span())
        || !is_always_in_haystack(codebase, &needle_values, haystack_type)
    {
        return;
    }

    context.collector.report_with_code(
        IssueCode::RedundantTypeComparison,
        Issue::warning(format!(
            "Redundant `in_array()` call: the needle of type `{needle_type_id}` is always in the haystack."
        ))
        .with_annotation(Annotation::primary(function_call.span()).with_message("This call always returns `true`."))
        .with_help("Consider removing this check as it has no effect."),
    );
}

fn is_in_array<A>(context: &Context<'_, '_, A>, function_call: &FunctionCall<'_>) -> bool
where
    A: Arena,
{
    let Expression::Identifier(identifier) = function_call.function else {
        return false;
    };

    (identifier.is_local() && ascii_lowercase_word(identifier.value()).as_bytes() == b"in_array")
        || ascii_lowercase_word(context.resolved_names.get(identifier)).as_bytes() == b"in_array"
}

fn get_arguments<'ast, 'arena>(
    function_call: &'ast FunctionCall<'arena>,
) -> Option<(&'ast Expression<'arena>, &'ast Expression<'arena>, Option<&'ast Expression<'arena>>)> {
    let mut slots: [Option<&Expression<'arena>>; 3] = [None, None, None];
    for (position, argument) in function_call.argument_list.arguments.iter().enumerate() {
        let slot = match argument {
            Argument::Positional(positional) if positional.ellipsis.is_none() => position,
            Argument::Positional(_) => return None,
            Argument::Named(named) => match named.name.value.to_ascii_lowercase().as_slice() {
                b"needle" => 0,
                b"haystack" => 1,
                b"strict" => 2,
                _ => return None,
            },
        };

        *slots.get_mut(slot)? = Some(argument.value());
    }

    Some((slots[0]?, slots[1]?, slots[2]))
}

fn get_finite_values(codebase: &CodebaseMetadata, union: &TUnion) -> Option<Vec<FiniteValue>> {
    let mut values = vec![];
    for atomic in union.types.as_ref() {
        for value in get_atomic_finite_values(codebase, atomic)? {
            if !values.contains(&value) {
                values.push(value);
            }
        }
    }

    (!values.is_empty() && values.len() <= MAX_FINITE_VALUES).then_some(values)
}

fn get_atomic_finite_values(codebase: &CodebaseMetadata, atomic: &TAtomic) -> Option<Vec<FiniteValue>> {
    let value = match atomic {
        TAtomic::Null => FiniteValue::Null,
        TAtomic::Scalar(TScalar::String(string)) => FiniteValue::String(string.get_known_literal_value()?.to_vec()),
        TAtomic::Scalar(TScalar::ClassLikeString(TClassLikeString::Literal { value })) => {
            FiniteValue::String(value.as_bytes().to_vec())
        }
        TAtomic::Scalar(TScalar::Integer(TInteger::Range(from, to)))
            if usize::try_from(to.checked_sub(*from)?).ok()? <= MAX_FINITE_VALUES =>
        {
            return Some((*from..=*to).map(FiniteValue::Int).collect());
        }
        TAtomic::Scalar(TScalar::Integer(integer)) => FiniteValue::Int(integer.get_literal_value()?),
        TAtomic::Scalar(TScalar::Float(float)) => FiniteValue::Float(float.get_literal_value()?),
        TAtomic::Scalar(TScalar::Bool(boolean)) => match boolean.value {
            Some(value) => FiniteValue::Bool(value),
            None => return Some(vec![FiniteValue::Bool(true), FiniteValue::Bool(false)]),
        },
        TAtomic::Object(TObject::Enum(r#enum)) => match r#enum.case {
            Some(case) => FiniteValue::EnumCase(ascii_lowercase_word(r#enum.name.as_bytes()), case),
            None => return get_enum_cases(codebase, r#enum.name),
        },
        TAtomic::Object(TObject::Named(named)) if named.intersection_types.is_none() => {
            return get_enum_cases(codebase, named.name);
        }
        _ => return None,
    };

    Some(vec![value])
}

fn get_enum_cases(codebase: &CodebaseMetadata, name: Word) -> Option<Vec<FiniteValue>> {
    let class_like = codebase.get_class_like(name.as_bytes())?;
    if !class_like.kind.is_enum() || class_like.enum_cases.is_empty() {
        return None;
    }

    let name = ascii_lowercase_word(name.as_bytes());

    Some(class_like.enum_cases.keys().map(|case| FiniteValue::EnumCase(name, *case)).collect())
}

fn haystack_holds_only_enums(codebase: &CodebaseMetadata, haystack_type: &TUnion) -> bool {
    haystack_type.types.iter().all(|atomic| {
        let TAtomic::Array(array) = atomic else {
            return false;
        };

        let value_type = get_array_value_parameter(array, codebase);

        !value_type.types.is_empty()
            && value_type.types.iter().all(|value| match value {
                TAtomic::Object(TObject::Enum(_)) => true,
                TAtomic::Object(TObject::Named(named)) => {
                    codebase.get_class_like(named.name.as_bytes()).is_some_and(|class_like| class_like.kind.is_enum())
                }
                _ => false,
            })
    })
}

fn is_never_in_haystack(codebase: &CodebaseMetadata, needle_type: &TUnion, haystack_type: &TUnion) -> bool {
    let mut arrays = vec![];
    for atomic in haystack_type.types.as_ref() {
        let TAtomic::Array(array) = atomic else {
            return false;
        };

        arrays.push(array);
    }

    if arrays.is_empty() {
        return false;
    }

    let value_type = arrays.iter().fold(get_never(), |value_type, array| {
        combine_union_types(
            &value_type,
            &get_array_value_parameter(array, codebase),
            codebase,
            CombinerOptions::default(),
        )
    });

    let finite_values = get_finite_values(codebase, needle_type).zip(get_finite_values(codebase, &value_type));
    if arrays.iter().all(|array| !array.is_sealed() && is_sure_to_be_non_empty(array)) {
        return finite_values.is_some_and(|(needle_values, haystack_values)| {
            matches!(
                (needle_values.as_slice(), haystack_values.as_slice()),
                ([needle_value], [haystack_value]) if needle_value != haystack_value
            )
        });
    }

    value_type.is_never()
        || finite_values.is_some_and(|(needle_values, haystack_values)| {
            needle_values.iter().all(|value| !haystack_values.contains(value))
        })
        || !can_expression_types_be_identical(codebase, needle_type, &value_type, false, false)
}

fn is_sure_to_be_non_empty(array: &TArray) -> bool {
    array.is_non_empty()
        || match array {
            TArray::List(list) => {
                list.get_known_elements().is_some_and(|elements| elements.values().any(|(optional, _)| !optional))
            }
            TArray::Keyed(keyed) => {
                keyed.get_known_items().is_some_and(|items| items.values().any(|(optional, _)| !optional))
            }
        }
}

fn is_always_in_haystack(codebase: &CodebaseMetadata, needle_values: &[FiniteValue], haystack_type: &TUnion) -> bool {
    !haystack_type.types.is_empty()
        && haystack_type.types.iter().all(|atomic| {
            let TAtomic::Array(array) = atomic else {
                return false;
            };

            let guaranteed = get_guaranteed_values(codebase, array);

            needle_values.iter().all(|value| guaranteed.contains(value))
        })
}

/// The values a haystack is sure to hold: those of its required entries that hold a single value,
/// or, for a non-empty array without known entries, its value type when that is a single value.
fn get_guaranteed_values(codebase: &CodebaseMetadata, array: &TArray) -> Vec<FiniteValue> {
    let entries: Vec<&(bool, TUnion)> = match array {
        TArray::List(list) => list.get_known_elements().map(|elements| elements.values().collect()).unwrap_or_default(),
        TArray::Keyed(keyed) => keyed.get_known_items().map(|items| items.values().collect()).unwrap_or_default(),
    };

    if entries.is_empty() {
        if !array.is_non_empty() {
            return vec![];
        }

        return get_single_finite_value(codebase, &get_array_value_parameter(array, codebase)).into_iter().collect();
    }

    entries
        .into_iter()
        .filter(|(optional, _)| !optional)
        .filter_map(|(_, value_type)| get_single_finite_value(codebase, value_type))
        .collect()
}

fn get_single_finite_value(codebase: &CodebaseMetadata, union: &TUnion) -> Option<FiniteValue> {
    match get_finite_values(codebase, union)?.as_slice() {
        [value] => Some(value.clone()),
        _ => None,
    }
}
