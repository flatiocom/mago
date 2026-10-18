use std::collections::BTreeMap;
use std::sync::Arc;

use mago_word::Word;

use crate::metadata::CodebaseMetadata;
use crate::ttype::atomic::TAtomic;
use crate::ttype::atomic::array::TArray;
use crate::ttype::atomic::array::keyed::TKeyedArray;
use crate::ttype::expander::StaticClassType;
use crate::ttype::expander::TypeExpansionOptions;
use crate::ttype::expander::expand_union;

pub(super) fn resolve(codebase: &mut CodebaseMetadata) {
    let class_constants: Vec<(Word, Word)> = codebase
        .class_likes
        .iter()
        .flat_map(|(class_name, metadata)| {
            metadata
                .constants
                .iter()
                .filter(|(_, constant)| constant.deferred_array_items.is_some())
                .map(|(constant_name, _)| (*class_name, *constant_name))
        })
        .collect();

    for (class_name, constant_name) in class_constants {
        let Some(array) = resolve_class_constant(codebase, class_name, constant_name) else {
            continue;
        };

        if let Some(constant) =
            codebase.class_likes.get_mut(&class_name).and_then(|metadata| metadata.constants.get_mut(&constant_name))
        {
            constant.inferred_type = Some(array);
        }
    }
}

fn resolve_class_constant(codebase: &CodebaseMetadata, class_name: Word, constant_name: Word) -> Option<TAtomic> {
    let metadata = codebase.class_likes.get(&class_name)?;
    let items = metadata.constants.get(&constant_name)?.deferred_array_items.as_ref()?;
    let options = TypeExpansionOptions {
        self_class: Some(metadata.name),
        static_class_type: StaticClassType::Name(metadata.name),
        ..Default::default()
    };

    let mut known_items = BTreeMap::new();
    for (key, value) in items {
        let mut key = key.clone();
        expand_union(codebase, &mut key, &options);

        known_items.insert(key.get_single_array_key()?, (false, value.clone()));
    }

    let mut keyed_array = TKeyedArray::new();
    keyed_array.non_empty = !known_items.is_empty();
    keyed_array.known_items = Some(Arc::new(known_items));

    Some(TAtomic::Array(TArray::Keyed(keyed_array)))
}
