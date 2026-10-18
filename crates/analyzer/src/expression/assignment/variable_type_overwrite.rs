use std::sync::Arc;

use mago_allocator::Arena;
use mago_codex::metadata::CodebaseMetadata;
use mago_codex::ttype::TType;
use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::atomic::array::TArray;
use mago_codex::ttype::atomic::array::keyed::TKeyedArray;
use mago_codex::ttype::atomic::object::TObject;
use mago_codex::ttype::atomic::object::r#enum::TEnum;
use mago_codex::ttype::atomic::scalar::TScalar;
use mago_codex::ttype::atomic::scalar::class_like_string::TClassLikeString;
use mago_codex::ttype::atomic::scalar::string::TString;
use mago_codex::ttype::combiner;
use mago_codex::ttype::comparator::ComparisonResult;
use mago_codex::ttype::comparator::union_comparator;
use mago_codex::ttype::get_array_parameters;
use mago_codex::ttype::union::TUnion;
use mago_reporting::Annotation;
use mago_reporting::Issue;
use mago_span::HasSpan;
use mago_syntax::cst::Assignment;
use mago_syntax::cst::AssignmentOperator;
use mago_syntax::cst::Expression;
use mago_syntax::cst::UnaryPrefix;
use mago_syntax::cst::UnaryPrefixOperator;
use mago_syntax::cst::Variable;
use mago_word::Word;

use crate::artifacts::AnalysisArtifacts;
use crate::code::IssueCode;
use crate::context::Context;
use crate::context::block::BlockContext;
use crate::utils::expression::get_block_expression_id;

pub(super) struct OverwrittenVariable {
    id: Word,
    previous_type: TUnion,
}

pub(super) fn find_overwritten_variable<'ctx, 'arena, A>(
    assignment: &Assignment<'arena>,
    context: &Context<'ctx, 'arena, A>,
    block_context: &BlockContext<'ctx>,
) -> Option<OverwrittenVariable>
where
    A: Arena,
{
    if !context.settings.check_variable_type_overwrite
        || !matches!(assignment.operator, AssignmentOperator::Assign(_))
        || !matches!(assignment.lhs, Expression::Variable(Variable::Direct(_)))
        || matches!(
            assignment.rhs,
            Expression::UnaryPrefix(UnaryPrefix { operator: UnaryPrefixOperator::Reference(_), .. })
        )
    {
        return None;
    }

    let id = get_block_expression_id(assignment.lhs, context, block_context)?;
    let previous_type = block_context.locals.get(&id)?;
    if previous_type.possibly_undefined() {
        return None;
    }

    Some(OverwrittenVariable { id, previous_type: (**previous_type).clone() })
}

pub(super) fn report_type_change<'arena, A>(
    assignment: &Assignment<'arena>,
    variable: OverwrittenVariable,
    context: &mut Context<'_, 'arena, A>,
    artifacts: &AnalysisArtifacts,
) where
    A: Arena,
{
    let Some(new_type) = artifacts.get_expression_type(assignment.rhs) else {
        return;
    };

    let codebase = context.codebase;
    let previous_type = generalize(&variable.previous_type, codebase);
    let new_type = generalize(new_type, codebase);
    if is_ignored(&previous_type) || is_ignored(&new_type) {
        return;
    }

    if is_subtype(&new_type, &previous_type, codebase) || is_subtype(&previous_type, &new_type, codebase) {
        return;
    }

    let variable_id = variable.id;
    let previous_type_id = previous_type.get_id();
    let new_type_id = new_type.get_id();
    context.collector.report_with_code(
        IssueCode::ShipmonkVariableTypeOverwritten,
        Issue::error(format!(
            "Overwriting variable `{variable_id}` while changing its type from `{previous_type_id}` to `{new_type_id}`."
        ))
        .with_annotation(
            Annotation::primary(assignment.span())
                .with_message(format!("`{variable_id}` changes from `{previous_type_id}` to `{new_type_id}`")),
        )
        .with_help("Assign the new value to a new variable."),
    );
}

fn is_ignored(union: &TUnion) -> bool {
    union.is_null() || union.is_mixed()
}

fn is_subtype(input: &TUnion, container: &TUnion, codebase: &CodebaseMetadata) -> bool {
    union_comparator::is_contained_by(codebase, input, container, false, false, true, &mut ComparisonResult::new())
}

fn generalize(union: &TUnion, codebase: &CodebaseMetadata) -> TUnion {
    let mut widened = union.clone();
    widened.widen_scalars();

    let is_null = widened.is_null();
    let mut atomics = Vec::with_capacity(widened.types.len());
    for atomic in widened.types.iter() {
        match atomic {
            TAtomic::Null if !is_null => {}
            TAtomic::Scalar(TScalar::Numeric) => atomics.extend([
                TAtomic::Scalar(TScalar::int()),
                TAtomic::Scalar(TScalar::float()),
                TAtomic::Scalar(TScalar::string()),
            ]),
            atomic => atomics.push(generalize_atomic(atomic, codebase)),
        }
    }

    TUnion::from_vec(combiner::combine(atomics, codebase, combiner::CombinerOptions::default()))
}

fn generalize_atomic(atomic: &TAtomic, codebase: &CodebaseMetadata) -> TAtomic {
    match atomic {
        TAtomic::Scalar(TScalar::ClassLikeString(TClassLikeString::Literal { .. })) => {
            TAtomic::Scalar(TScalar::String(TString::general()))
        }
        TAtomic::Object(TObject::Enum(enum_case)) => {
            TAtomic::Object(TObject::Enum(TEnum { name: enum_case.name, case: None }))
        }
        TAtomic::Object(TObject::Named(named)) => {
            let mut named = named.clone();
            named.is_static = false;
            named.is_this = false;
            if let Some(type_parameters) = named.type_parameters.as_mut() {
                for type_parameter in type_parameters.iter_mut() {
                    *type_parameter = generalize(type_parameter, codebase);
                }
            }

            TAtomic::Object(TObject::Named(named))
        }
        TAtomic::Iterable(iterable) => {
            let mut iterable = iterable.clone();
            iterable.key_type = Arc::new(generalize(&iterable.key_type, codebase));
            iterable.value_type = Arc::new(generalize(&iterable.value_type, codebase));

            TAtomic::Iterable(iterable)
        }
        TAtomic::Array(array) => TAtomic::Array(generalize_array(array, codebase)),
        _ => atomic.clone(),
    }
}

fn generalize_array(array: &TArray, codebase: &CodebaseMetadata) -> TArray {
    if array.is_empty() {
        return array.clone();
    }

    if let TArray::Keyed(keyed) = array
        && keyed.has_known_items()
        && !array.is_sealed()
    {
        return generalize_unsealed_shape(keyed, codebase);
    }

    let (key_type, value_type) = get_array_parameters(array, codebase);

    TArray::Keyed(TKeyedArray::new_with_parameters(
        Arc::new(generalize(&key_type, codebase)),
        Arc::new(generalize(&value_type, codebase)),
    ))
}

fn generalize_unsealed_shape(keyed: &TKeyedArray, codebase: &CodebaseMetadata) -> TArray {
    let mut keyed = keyed.clone();
    if let Some((key_type, value_type)) = keyed.parameters.as_mut() {
        *key_type = Arc::new(generalize(key_type, codebase));
        *value_type = Arc::new(generalize(value_type, codebase));
    }
    if let Some(known_items) = keyed.known_items.as_mut().map(Arc::make_mut) {
        for (_, item_type) in known_items.values_mut() {
            *item_type = generalize(item_type, codebase);
        }
    }

    TArray::Keyed(keyed)
}
