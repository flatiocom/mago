use mago_word::Word;
use mago_word::WordMap;
use mago_word::WordSet;

use crate::metadata::CodebaseMetadata;
use crate::metadata::class_like::ClassLikeMetadata;
use crate::reference::SymbolReferences;
use crate::ttype::atomic::TAtomic;
use crate::ttype::atomic::reference::TReference;

use super::merge::merge_metadata_from_required_class_like;

/// A trait without `@require-extends` requires the most specific class its users share, where PHPStan sees `$this`.
pub fn require_shared_host_classes(
    codebase: &mut CodebaseMetadata,
    traits_to_repopulate: &WordSet,
    symbol_references: &mut SymbolReferences,
) {
    let mut users: WordMap<Vec<Word>> = WordMap::default();
    for (class_name, metadata) in &codebase.class_likes {
        if metadata.kind.is_trait() || metadata.name != *class_name {
            continue;
        }

        for used_trait in &metadata.used_traits {
            if traits_to_repopulate.contains(used_trait) {
                users.entry(*used_trait).or_default().push(*class_name);
            }
        }
    }

    let mut hosts: Vec<(Word, Word)> = users
        .into_iter()
        .filter(|(trait_name, _)| {
            codebase.class_likes.get(trait_name).is_some_and(|metadata| {
                metadata.kind.is_trait() && !metadata.flags.is_built_in() && metadata.require_extends.is_empty()
            })
        })
        .filter_map(|(trait_name, trait_users)| {
            let host = shared_class(codebase, &trait_users)?;
            let mixin_is_narrower = codebase.class_likes.get(&trait_name).is_some_and(|metadata| {
                mixin_classes(metadata).any(|mixin| {
                    codebase.is_instance_of(mixin.as_bytes(), host.as_bytes())
                        && !codebase.is_instance_of(host.as_bytes(), mixin.as_bytes())
                })
            });

            (!mixin_is_narrower).then_some((trait_name, host))
        })
        .collect();
    hosts.sort_unstable();

    for (trait_name, host) in hosts {
        let Some(mut metadata) = codebase.class_likes.remove(&trait_name) else {
            continue;
        };

        metadata.require_extends.insert(host);
        merge_metadata_from_required_class_like(&mut metadata, codebase, host, symbol_references);
        codebase.class_likes.insert(trait_name, metadata);
    }
}

/// A trait's required class follows its users, so a trait is repopulated with them.
pub fn traits_used_by(codebase: &CodebaseMetadata, classes: &WordSet) -> Vec<Word> {
    classes
        .iter()
        .filter_map(|class_name| codebase.class_likes.get(class_name))
        .flat_map(|metadata| metadata.used_traits.iter().copied())
        .filter(|used_trait| {
            codebase
                .class_likes
                .get(used_trait)
                .is_some_and(|metadata| metadata.kind.is_trait() && !metadata.flags.is_built_in())
        })
        .collect()
}

fn shared_class(codebase: &CodebaseMetadata, users: &[Word]) -> Option<Word> {
    let first = *users.iter().min()?;
    let mut candidate = Some(first);
    while let Some(class_name) = candidate {
        let shared = users.iter().all(|user| {
            *user == class_name
                || codebase
                    .class_likes
                    .get(user)
                    .is_some_and(|metadata| metadata.all_parent_classes.contains(&class_name))
        });
        if shared {
            return Some(class_name);
        }

        candidate = codebase.class_likes.get(&class_name).and_then(|metadata| metadata.direct_parent_class);
    }

    None
}

fn mixin_classes(metadata: &ClassLikeMetadata) -> impl Iterator<Item = Word> + '_ {
    metadata.mixins.iter().flat_map(|mixin| mixin.type_union.types.iter()).filter_map(|atomic| match atomic {
        TAtomic::Reference(TReference::Symbol { name, .. }) => Some(*name),
        other => other.get_object_or_enum_name(),
    })
}
