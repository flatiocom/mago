use foldhash::HashMap;
use mago_allocator::Arena;
use mago_word::Word;
use mago_word::WordSet;

use mago_codex::metadata::CodebaseMetadata;
use mago_codex::metadata::class_like::ClassLikeMetadata;
use mago_codex::ttype::TType;
use mago_codex::ttype::add_union_type;
use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::atomic::scalar::TScalar;
use mago_codex::ttype::combiner::CombinerOptions;
use mago_codex::ttype::comparator::union_comparator::can_expression_types_be_identical;
use mago_codex::ttype::expander::StaticClassType;
use mago_codex::ttype::expander::TypeExpansionOptions;
use mago_codex::ttype::expander::expand_union;
use mago_codex::ttype::get_null;
use mago_codex::ttype::union::TUnion;
use mago_reporting::Annotation;
use mago_reporting::Issue;
use mago_span::Span;

use crate::code::IssueCode;
use crate::context::Context;

#[derive(Debug, Default, Clone)]
pub(crate) struct AssignedPropertyTypes {
    types: HashMap<(Word, Word), TUnion>,
    classes_with_unresolved_writes: WordSet,
}

impl AssignedPropertyTypes {
    pub(crate) fn record(
        &mut self,
        declaring_class: Word,
        property_name: Word,
        assigned_type: &TUnion,
        codebase: &CodebaseMetadata,
    ) {
        let key = (declaring_class, property_name);
        let combined = match self.types.remove(&key) {
            Some(existing) => add_union_type(existing, assigned_type, codebase, CombinerOptions::default()),
            None => assigned_type.clone(),
        };

        self.types.insert(key, combined);
    }

    pub(crate) fn record_unresolved_write(&mut self, class_name: Option<Word>) {
        if let Some(class_name) = class_name {
            self.classes_with_unresolved_writes.insert(class_name);
        }
    }

    pub(crate) fn absorb(&mut self, other: Self, codebase: &CodebaseMetadata) {
        for ((declaring_class, property_name), assigned_type) in other.types {
            self.record(declaring_class, property_name, &assigned_type, codebase);
        }

        self.classes_with_unresolved_writes.extend(other.classes_with_unresolved_writes);
    }
}

pub(crate) fn check_unused_property_types<A>(
    class_like_metadata: &ClassLikeMetadata,
    assigned_property_types: &AssignedPropertyTypes,
    context: &mut Context<'_, '_, A>,
) where
    A: Arena,
{
    let class_name = class_like_metadata.name;
    if class_like_metadata.kind.is_trait()
        || class_like_metadata.kind.is_interface()
        || class_like_metadata.direct_trait_names().next().is_some()
        || assigned_property_types.classes_with_unresolved_writes.contains(&class_name)
    {
        return;
    }

    for (property_name, property) in &class_like_metadata.properties {
        if class_like_metadata
            .declaring_property_ids
            .get(property_name)
            .is_some_and(|declaring| *declaring != class_name)
            || !property.read_visibility.is_private()
            || property.flags.is_promoted_property()
            || !property.hooks.is_empty()
            || class_like_metadata
                .used_traits
                .iter()
                .any(|trait_name| context.codebase.property_exists(trait_name.as_bytes(), property_name.as_bytes()))
        {
            continue;
        }

        let Some(type_metadata) = &property.type_metadata else {
            continue;
        };

        let narrowable_bool = context.settings.find_overly_wide_bool_property_types
            && matches!(type_metadata.type_union.types.as_ref(), [TAtomic::Scalar(TScalar::Bool(declared))] if declared.is_general());

        if !narrowable_bool
            && (type_metadata.type_union.types.len() < 2
                || type_metadata.type_union.has_mixed()
                || type_metadata.type_union.has_template_types())
        {
            continue;
        }

        let mut assigned_type = assigned_property_types.types.get(&(class_name, *property_name)).cloned();
        if property.type_declaration_metadata.is_none() && !property.flags.has_default() {
            assigned_type = Some(match assigned_type {
                Some(assigned_type) => {
                    add_union_type(assigned_type, &get_null(), context.codebase, CombinerOptions::default())
                }
                None => get_null(),
            });
        }

        let Some(assigned_type) = assigned_type else {
            continue;
        };

        let mut declared_type = type_metadata.type_union.clone();
        expand_union(
            context.codebase,
            &mut declared_type,
            &TypeExpansionOptions {
                self_class: Some(class_like_metadata.original_name),
                static_class_type: StaticClassType::Name(class_like_metadata.original_name),
                ..Default::default()
            },
        );

        if declared_type.types.iter().all(is_constant_scalar) && assigned_type.types.iter().all(is_constant_scalar) {
            continue;
        }

        let Some(property_span) = property.span.or(property.name_span) else {
            continue;
        };

        if narrowable_bool {
            let can_be_true = can_be_assigned(context.codebase, &TAtomic::Scalar(TScalar::r#true()), &assigned_type);
            let can_be_false = can_be_assigned(context.codebase, &TAtomic::Scalar(TScalar::r#false()), &assigned_type);
            if can_be_true != can_be_false {
                report_overly_wide_bool_property_type(
                    context,
                    class_like_metadata.original_name,
                    *property_name,
                    can_be_true,
                    property_span,
                );
            }

            continue;
        }

        for declared_part in declared_type.types.iter() {
            if can_be_assigned(context.codebase, declared_part, &assigned_type) {
                continue;
            }

            report_unused_property_type(
                context,
                class_like_metadata.original_name,
                *property_name,
                &declared_type,
                declared_part,
                property_span,
            );
        }
    }
}

fn can_be_assigned(codebase: &CodebaseMetadata, declared_part: &TAtomic, assigned_type: &TUnion) -> bool {
    let assigned_parts: Vec<TAtomic> = assigned_type
        .types
        .iter()
        .filter(|assigned_part| {
            !((declared_part.is_float() && assigned_part.is_int())
                || (declared_part.is_int() && assigned_part.is_float()))
        })
        .cloned()
        .collect();

    !assigned_parts.is_empty()
        && can_expression_types_be_identical(
            codebase,
            &TUnion::from_atomic(declared_part.clone()),
            &TUnion::from_vec(assigned_parts),
            false,
            false,
        )
}

fn is_constant_scalar(atomic: &TAtomic) -> bool {
    match atomic {
        TAtomic::Null => true,
        TAtomic::Scalar(scalar) => scalar.is_literal_value(),
        _ => false,
    }
}

fn report_unused_property_type<A>(
    context: &mut Context<'_, '_, A>,
    class_name: Word,
    property_name: Word,
    declared_type: &TUnion,
    unused_part: &TAtomic,
    property_span: Span,
) where
    A: Arena,
{
    let declared_type_id = declared_type.get_id();
    let unused_part_id = unused_part.get_id();

    context.collector.report_with_code(
        IssueCode::UnusedPropertyType,
        Issue::help(format!(
            "Property `{class_name}::{property_name}` (`{declared_type_id}`) is never assigned `{unused_part_id}` so it can be removed from the property type."
        ))
        .with_annotation(
            Annotation::primary(property_span)
                .with_message(format!("No assignment or default value gives this property a `{unused_part_id}`.")),
        )
        .with_help(format!("Remove `{unused_part_id}` from the property type, or assign it where it is meant to be.")),
    );
}

fn report_overly_wide_bool_property_type<A>(
    context: &mut Context<'_, '_, A>,
    class_name: Word,
    property_name: Word,
    only_assigned: bool,
    property_span: Span,
) where
    A: Arena,
{
    let never_assigned = !only_assigned;

    context.collector.report_with_code(
        IssueCode::OverlyWideBoolPropertyType,
        Issue::help(format!(
            "Property `{class_name}::{property_name}` (`bool`) is never assigned `{never_assigned}` so the property type can be changed to `{only_assigned}`."
        ))
        .with_annotation(
            Annotation::primary(property_span)
                .with_message(format!("No assignment or default value gives this property `{never_assigned}`.")),
        )
        .with_help(format!("Change the property type to `{only_assigned}`, or assign `{never_assigned}` where it is meant to be.")),
    );
}
