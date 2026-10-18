use std::rc::Rc;

use mago_word::WordMap;
use mago_word::WordSet;

use mago_codex::metadata::CodebaseMetadata;
use mago_codex::ttype::combine_union_types;
use mago_codex::ttype::combiner::CombinerOptions;
use mago_codex::ttype::union::TUnion;
use mago_span::Span;

use crate::context::scope::control_action::ControlActionSet;

#[derive(Clone, Debug)]
pub struct LoopScope {
    pub span: Span,
    pub iteration_count: usize,
    pub parent_context_variables: WordMap<Rc<TUnion>>,
    pub redefined_loop_variables: WordMap<Rc<TUnion>>,
    pub possibly_redefined_loop_variables: WordMap<Rc<TUnion>>,
    pub possibly_redefined_loop_parent_variables: WordMap<Rc<TUnion>>,
    pub possibly_defined_loop_parent_variables: WordMap<Rc<TUnion>>,
    pub by_reference_loop_mutations: WordMap<Rc<TUnion>>,
    pub assignment_targets: WordSet,
    pub tracks_assignment_targets: bool,
    pub variables_possibly_in_scope: WordSet,
    pub final_actions: ControlActionSet,
    pub truthy_pre_conditions: bool,
    pub condition_always_false: bool,
    pub parent_loop: Option<Box<LoopScope>>,
}

impl LoopScope {
    pub fn new(span: Span, parent_context_vars: WordMap<Rc<TUnion>>, parent_loop: Option<Box<LoopScope>>) -> Self {
        Self {
            span,
            parent_context_variables: parent_context_vars,
            iteration_count: 0,
            redefined_loop_variables: WordMap::default(),
            possibly_redefined_loop_variables: WordMap::default(),
            possibly_redefined_loop_parent_variables: WordMap::default(),
            possibly_defined_loop_parent_variables: WordMap::default(),
            by_reference_loop_mutations: WordMap::default(),
            assignment_targets: WordSet::default(),
            tracks_assignment_targets: false,
            final_actions: ControlActionSet::new(),
            variables_possibly_in_scope: WordSet::default(),
            parent_loop,
            truthy_pre_conditions: true,
            condition_always_false: false,
        }
    }

    pub fn with_parent_loop(self, parent_loop: Option<Box<LoopScope>>) -> Self {
        Self { parent_loop, ..self }
    }

    pub fn record_possibly_defined_loop_parent_variables(
        &mut self,
        locals: &WordMap<Rc<TUnion>>,
        codebase: &CodebaseMetadata,
    ) {
        if self.iteration_count != 0 {
            return;
        }

        for (var_id, var_type) in locals {
            if self.parent_context_variables.contains_key(var_id) {
                continue;
            }

            let resulting_type = match self.possibly_defined_loop_parent_variables.get(var_id) {
                Some(existing_type) => {
                    Rc::new(combine_union_types(existing_type, var_type, codebase, CombinerOptions::default()))
                }
                None => Rc::clone(var_type),
            };

            self.possibly_defined_loop_parent_variables.insert(*var_id, resulting_type);
        }
    }
}
