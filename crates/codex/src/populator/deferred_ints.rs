use mago_word::Word;

use crate::metadata::CodebaseMetadata;
use crate::ttype::atomic::TAtomic;
use crate::ttype::expander::StaticClassType;
use crate::ttype::expander::TypeExpansionOptions;
use crate::ttype::union::TUnion;

const MAXIMUM_PASSES: usize = 16;

pub(super) fn resolve(codebase: &mut CodebaseMetadata) {
    let class_constants: Vec<(Word, Word)> = codebase
        .class_likes
        .iter()
        .flat_map(|(class_name, metadata)| {
            metadata
                .constants
                .iter()
                .filter(|(_, constant)| constant.deferred_int_value.is_some())
                .map(|(constant_name, _)| (*class_name, *constant_name))
        })
        .collect();
    let constants: Vec<Word> = codebase
        .constants
        .iter()
        .filter(|(_, constant)| constant.deferred_int_value.is_some())
        .map(|(constant_name, _)| *constant_name)
        .collect();

    if class_constants.is_empty() && constants.is_empty() {
        return;
    }

    for _ in 0..MAXIMUM_PASSES {
        let class_constant_values: Vec<Option<TAtomic>> = class_constants
            .iter()
            .map(|(class_name, constant_name)| resolve_class_constant(codebase, *class_name, *constant_name))
            .collect();
        let constant_values: Vec<Option<TUnion>> =
            constants.iter().map(|constant_name| resolve_constant(codebase, *constant_name)).collect();

        let mut changed = false;
        for ((class_name, constant_name), value) in class_constants.iter().zip(class_constant_values) {
            if let Some(constant) =
                codebase.class_likes.get_mut(class_name).and_then(|metadata| metadata.constants.get_mut(constant_name))
                && value.is_some()
                && constant.inferred_type != value
            {
                constant.inferred_type = value;
                changed = true;
            }
        }

        for (constant_name, value) in constants.iter().zip(constant_values) {
            if let Some(constant) = codebase.constants.get_mut(constant_name)
                && value.is_some()
                && constant.inferred_type != value
            {
                constant.inferred_type = value;
                changed = true;
            }
        }

        if !changed {
            return;
        }
    }
}

fn resolve_class_constant(codebase: &CodebaseMetadata, class_name: Word, constant_name: Word) -> Option<TAtomic> {
    let metadata = codebase.class_likes.get(&class_name)?;
    let deferred = metadata.constants.get(&constant_name)?.deferred_int_value.as_ref()?;

    Some(deferred.resolve_atomic(
        codebase,
        &TypeExpansionOptions {
            self_class: Some(metadata.name),
            static_class_type: StaticClassType::Name(metadata.name),
            ..Default::default()
        },
    ))
}

fn resolve_constant(codebase: &CodebaseMetadata, constant_name: Word) -> Option<TUnion> {
    let deferred = codebase.constants.get(&constant_name)?.deferred_int_value.as_ref()?;

    Some(deferred.resolve(codebase, &TypeExpansionOptions::default()))
}
