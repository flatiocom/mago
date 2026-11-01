use std::rc::Rc;

use mago_codex::metadata::CodebaseMetadata;
use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::atomic::array::TArray;
use mago_codex::ttype::combine_union_types;
use mago_codex::ttype::combiner::CombinerOptions;
use mago_codex::ttype::comparator::ComparisonResult;
use mago_codex::ttype::comparator::union_comparator;
use mago_codex::ttype::union::TUnion;
use mago_word::Word;
use mago_word::WordMap;
use mago_word::WordSet;

use crate::artifacts::is_plain_variable;
use crate::context::block::BlockContext;
use crate::context::scope::var_has_root;

/// A link a branch merge leaves between two variables, like PHPStan's conditional expressions:
/// once `guard` is narrowed to a type it cannot have at the end of the other branch, the merge
/// came through this branch, so `target` has this branch's type.
#[derive(Clone, Debug, PartialEq, Eq)]
pub struct ConditionalExpression {
    pub guard: Word,
    pub other_branch_guard_type: Rc<TUnion>,
    pub target: Word,
    pub merged_type: Rc<TUnion>,
    pub branch_type: Rc<TUnion>,
}

#[derive(Clone, Debug, Default)]
pub struct BranchEnd {
    locals: WordMap<Rc<TUnion>>,
    possibly_undefined_variable_ids: WordSet,
    conditional_expressions: Vec<Rc<ConditionalExpression>>,
}

impl BranchEnd {
    pub fn of(block_context: &BlockContext<'_>) -> Self {
        Self::new(
            &block_context.locals,
            block_context.possibly_undefined_variable_ids.clone(),
            block_context.conditional_expressions.clone(),
        )
    }

    pub fn new(
        locals: &WordMap<Rc<TUnion>>,
        possibly_undefined_variable_ids: WordSet,
        conditional_expressions: Vec<Rc<ConditionalExpression>>,
    ) -> Self {
        Self {
            locals: locals
                .iter()
                .filter(|(variable_id, _)| is_plain_variable(**variable_id))
                .map(|(variable_id, variable_type)| (*variable_id, Rc::clone(variable_type)))
                .collect(),
            possibly_undefined_variable_ids,
            conditional_expressions,
        }
    }

    fn merge(self, codebase: &CodebaseMetadata, other: &BranchEnd) -> BranchEnd {
        let mut locals = WordMap::default();
        let mut possibly_undefined_variable_ids = self
            .possibly_undefined_variable_ids
            .union(&other.possibly_undefined_variable_ids)
            .copied()
            .collect::<WordSet>();
        for (variable_id, variable_type) in self.locals.iter().chain(other.locals.iter()) {
            if locals.contains_key(variable_id) {
                continue;
            }

            let merged_type = match (self.locals.get(variable_id), other.locals.get(variable_id)) {
                (Some(first_type), Some(second_type)) => {
                    combine_union_types(first_type, second_type, codebase, CombinerOptions::default())
                }
                _ => {
                    let mut possibly_undefined_type = (**variable_type).clone();
                    possibly_undefined_type.set_possibly_undefined(true, None);
                    possibly_undefined_variable_ids.insert(*variable_id);
                    possibly_undefined_type
                }
            };

            locals.insert(*variable_id, Rc::new(merged_type));
        }

        let mut merged = BranchEnd {
            locals,
            possibly_undefined_variable_ids,
            conditional_expressions: self
                .conditional_expressions
                .iter()
                .filter(|conditional_expression| other.conditional_expressions.contains(conditional_expression))
                .cloned()
                .collect(),
        };

        let links = create_merge_links(codebase, &self, other, Merged::of_branch(&merged));
        merged.conditional_expressions.extend(links);

        merged
    }

    fn defined_type(&self, variable_id: &Word) -> Option<&Rc<TUnion>> {
        let variable_type = self.locals.get(variable_id)?;
        let possibly_undefined = variable_type.possibly_undefined_from_try()
            || variable_type.possibly_undefined() && self.possibly_undefined_variable_ids.contains(variable_id);

        (!possibly_undefined).then_some(variable_type)
    }
}

#[derive(Clone, Copy)]
struct Merged<'merge> {
    locals: &'merge WordMap<Rc<TUnion>>,
    possibly_undefined_variable_ids: &'merge WordSet,
    conditional_expressions: &'merge [Rc<ConditionalExpression>],
}

impl<'merge> Merged<'merge> {
    fn of_block(block_context: &'merge BlockContext<'_>) -> Self {
        Self {
            locals: &block_context.locals,
            possibly_undefined_variable_ids: &block_context.possibly_undefined_variable_ids,
            conditional_expressions: &block_context.conditional_expressions,
        }
    }

    fn of_branch(branch_end: &'merge BranchEnd) -> Self {
        Self {
            locals: &branch_end.locals,
            possibly_undefined_variable_ids: &branch_end.possibly_undefined_variable_ids,
            conditional_expressions: &branch_end.conditional_expressions,
        }
    }
}

/// The links a merge of two branches into `merged` leaves, in both directions.
pub fn create_conditional_expressions(
    codebase: &CodebaseMetadata,
    first: &BranchEnd,
    second: &BranchEnd,
    merged: &BlockContext<'_>,
) -> Vec<Rc<ConditionalExpression>> {
    create_merge_links(codebase, first, second, Merged::of_block(merged))
}

pub fn create_chain_conditional_expressions(
    codebase: &CodebaseMetadata,
    branch_ends: &[BranchEnd],
    merged: &BlockContext<'_>,
) -> Vec<Rc<ConditionalExpression>> {
    let Some((last, earlier)) = branch_ends.split_last() else {
        return vec![];
    };

    let Some(accumulated) = earlier.iter().cloned().reduce(|accumulated, next| accumulated.merge(codebase, &next))
    else {
        return vec![];
    };

    create_conditional_expressions(codebase, &accumulated, last, merged)
}

fn create_merge_links(
    codebase: &CodebaseMetadata,
    first: &BranchEnd,
    second: &BranchEnd,
    merged: Merged<'_>,
) -> Vec<Rc<ConditionalExpression>> {
    let mut conditional_expressions = vec![];
    add_branch_conditional_expressions(codebase, first, second, merged, &mut conditional_expressions);
    add_branch_conditional_expressions(codebase, second, first, merged, &mut conditional_expressions);
    keep_vacuous_conditional_expressions(codebase, first, second, merged, &mut conditional_expressions);
    keep_vacuous_conditional_expressions(codebase, second, first, merged, &mut conditional_expressions);

    conditional_expressions
}

/// A link of one branch also holds after the merge when the other branch only leaves the guard
/// values that cannot fire it, like PHPStan's `preserveVacuousConditionalExpressions()`.
fn keep_vacuous_conditional_expressions(
    codebase: &CodebaseMetadata,
    branch: &BranchEnd,
    other_branch: &BranchEnd,
    merged: Merged<'_>,
    conditional_expressions: &mut Vec<Rc<ConditionalExpression>>,
) {
    for conditional_expression in &branch.conditional_expressions {
        if merged.conditional_expressions.contains(conditional_expression)
            || conditional_expressions.contains(conditional_expression)
            || !merged.locals.contains_key(&conditional_expression.target)
        {
            continue;
        }

        let Some(other_branch_guard_type) = other_branch.defined_type(&conditional_expression.guard) else {
            continue;
        };

        if is_contained_by(codebase, other_branch_guard_type, &conditional_expression.other_branch_guard_type) {
            conditional_expressions.push(Rc::clone(conditional_expression));
        }
    }
}

fn add_branch_conditional_expressions(
    codebase: &CodebaseMetadata,
    branch: &BranchEnd,
    other_branch: &BranchEnd,
    merged: Merged<'_>,
    conditional_expressions: &mut Vec<Rc<ConditionalExpression>>,
) {
    let guards = branch
        .locals
        .keys()
        .filter_map(|variable_id| {
            let branch_type = branch.defined_type(variable_id)?;
            let other_branch_type = other_branch.defined_type(variable_id)?;
            (merged.locals.contains_key(variable_id)
                && !is_contained_by(codebase, branch_type, other_branch_type)
                && !is_contained_by(codebase, other_branch_type, branch_type))
            .then(|| (*variable_id, other_branch_type, !may_hold_a_value_of(codebase, branch_type, other_branch_type)))
        })
        .collect::<Vec<_>>();

    if guards.is_empty() {
        return;
    }

    for (target, branch_type) in &branch.locals {
        if target.as_bytes() == b"$this" {
            continue;
        }

        let Some(merged_type) = merged.locals.get(target) else {
            continue;
        };

        let defined_in_branch = branch.defined_type(target).is_some();
        let defined_in_merge =
            !(merged_type.possibly_undefined() && merged.possibly_undefined_variable_ids.contains(target));
        if branch_type == merged_type && defined_in_branch == defined_in_merge {
            continue;
        }

        let defined_in_other_branch = other_branch.defined_type(target).is_some();
        for (guard, other_branch_guard_type, separates_branches) in &guards {
            if guard == target || defined_in_other_branch && !separates_branches {
                continue;
            }

            conditional_expressions.push(Rc::new(ConditionalExpression {
                guard: *guard,
                other_branch_guard_type: Rc::clone(other_branch_guard_type),
                target: *target,
                merged_type: Rc::clone(merged_type),
                branch_type: Rc::clone(branch_type),
            }));
        }
    }
}

impl BlockContext<'_> {
    /// Drops the links that guard or target `variable_id` or a path under it, which a write invalidates.
    pub(crate) fn forget_conditional_expressions(&mut self, variable_id: Word) {
        self.conditional_expressions.retain(|conditional_expression| {
            !var_has_root(conditional_expression.guard, variable_id)
                && !var_has_root(conditional_expression.target, variable_id)
        });
    }

    /// Narrows the targets of every link whose guard `changed_variable_ids` holds, and the targets
    /// of the links those guard, until no link applies.
    pub(crate) fn apply_conditional_expressions(
        &mut self,
        codebase: &CodebaseMetadata,
        changed_variable_ids: &mut WordSet,
    ) {
        if self.conditional_expressions.is_empty() {
            return;
        }

        let mut pending_guards = changed_variable_ids.iter().copied().collect::<Vec<_>>();
        while let Some(guard) = pending_guards.pop() {
            let Some(guard_type) = self.locals.get(&guard).cloned() else {
                continue;
            };

            if guard_type.is_never() {
                continue;
            }

            let applicable = self
                .conditional_expressions
                .iter()
                .filter(|conditional_expression| conditional_expression.guard == guard)
                .filter(|conditional_expression| {
                    !may_hold_a_value_of(codebase, &guard_type, &conditional_expression.other_branch_guard_type)
                })
                .cloned()
                .collect::<Vec<_>>();

            for conditional_expression in applicable {
                let Some(current_type) = self.locals.get(&conditional_expression.target).cloned() else {
                    continue;
                };

                let narrowed_type = narrow_target(codebase, &current_type, &conditional_expression);
                if narrowed_type == current_type {
                    continue;
                }

                if !narrowed_type.possibly_undefined() {
                    self.possibly_undefined_variable_ids.remove(&conditional_expression.target);
                }

                self.locals.insert(conditional_expression.target, narrowed_type);
                if changed_variable_ids.insert(conditional_expression.target) {
                    pending_guards.push(conditional_expression.target);
                }
            }
        }
    }
}

fn narrow_target(
    codebase: &CodebaseMetadata,
    current_type: &Rc<TUnion>,
    conditional_expression: &ConditionalExpression,
) -> Rc<TUnion> {
    let branch_type = &conditional_expression.branch_type;
    if current_type == &conditional_expression.merged_type || is_contained_by(codebase, branch_type, current_type) {
        return Rc::clone(branch_type);
    }

    if current_type.possibly_undefined() && !branch_type.possibly_undefined() {
        let mut defined_type = (**current_type).clone();
        defined_type.set_possibly_undefined(false, None);

        return Rc::new(defined_type);
    }

    Rc::clone(current_type)
}

fn is_contained_by(codebase: &CodebaseMetadata, input_type: &TUnion, container_type: &TUnion) -> bool {
    union_comparator::is_contained_by(
        codebase,
        input_type,
        container_type,
        false,
        false,
        false,
        &mut ComparisonResult::new(),
    )
}

fn may_hold_a_value_of(codebase: &CodebaseMetadata, narrowed_type: &TUnion, other_branch_type: &TUnion) -> bool {
    narrowed_type.types.iter().any(|narrowed_part| {
        other_branch_type.types.iter().any(|other_branch_part| {
            !separates_empty_array(narrowed_part, other_branch_part)
                && union_comparator::can_expression_types_be_identical(
                    codebase,
                    &TUnion::from_atomic(narrowed_part.clone()),
                    &TUnion::from_atomic(other_branch_part.clone()),
                    false,
                    false,
                )
        })
    })
}

fn separates_empty_array(first: &TAtomic, second: &TAtomic) -> bool {
    let (TAtomic::Array(first), TAtomic::Array(second)) = (first, second) else {
        return false;
    };

    first.is_empty() && is_never_empty(second) || second.is_empty() && is_never_empty(first)
}

fn is_never_empty(array: &TArray) -> bool {
    array.is_non_empty()
        || match array {
            TArray::Keyed(keyed_array) => {
                keyed_array.known_items.as_deref().is_some_and(|items| items.values().any(|(optional, _)| !optional))
            }
            TArray::List(list) => {
                list.known_elements.as_deref().is_some_and(|elements| elements.values().any(|(optional, _)| !optional))
            }
        }
}
