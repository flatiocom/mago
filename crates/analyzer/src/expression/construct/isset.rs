use mago_allocator::Arena;
use mago_codex::ttype::TType;
use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::get_bool;
use mago_codex::ttype::get_false;
use mago_codex::ttype::get_true;
use mago_codex::ttype::union::TUnion;
use mago_reporting::Annotation;
use mago_reporting::Issue;
use mago_span::HasSpan;
use mago_syntax::cst::Access;
use mago_syntax::cst::ClassLikeMemberSelector;
use mago_syntax::cst::Expression;
use mago_syntax::cst::IssetConstruct;
use mago_syntax::cst::Variable;
use mago_word::concat_word;
use mago_word::word;

use crate::analyzable::Analyzable;
use crate::artifacts::AnalysisArtifacts;
use crate::code::IssueCode;
use crate::context::Context;
use crate::context::block::BlockContext;
use crate::error::AnalysisError;
use crate::utils::expression::get_block_expression_id;
use crate::utils::misc::unwrap_expression;

impl<'ast, 'arena> Analyzable<'ast, 'arena> for IssetConstruct<'arena> {
    fn analyze<'ctx, A>(
        &'ast self,
        context: &mut Context<'ctx, 'arena, A>,
        block_context: &mut BlockContext<'ctx>,
        artifacts: &mut AnalysisArtifacts,
    ) -> Result<(), AnalysisError>
    where
        A: Arena,
    {
        let mut all_definitely_set = true;
        let mut any_definitely_unset = false;
        for value in &self.values {
            if !is_valid_isset_expression(value) {
                context.collector.report_with_code(
                    IssueCode::InvalidIssetExpression,
                    Issue::error("Cannot use `isset()` on the result of an expression.")
                        .with_annotation(
                            Annotation::primary(value.span()).with_message("This is not a variable or property"),
                        )
                        .with_note("The `isset()` function is designed to check if a variable, property, or array element is set and not null.")
                        .with_help("Consider using `null !== expression` for this check instead."),
                );
            }

            let was_inside_isset = block_context.flags.inside_isset();
            block_context.flags.set_inside_isset(true);
            value.analyze(context, block_context, artifacts)?;
            block_context.flags.set_inside_isset(was_inside_isset);
            report_redundant_isset(context, block_context, artifacts, value);
            if let Some(value_type) = artifacts.get_expression_type(value) {
                if value_type.is_never() || value_type.is_null() {
                    any_definitely_unset = true;
                    continue;
                }

                if value_type.possibly_undefined()
                    || value_type.possibly_undefined_from_try()
                    || value_type.can_be_null()
                {
                    all_definitely_set = false;
                }
            } else {
                all_definitely_set = false;
            }
        }

        let result_type = if any_definitely_unset {
            get_false()
        } else if all_definitely_set {
            get_true()
        } else {
            get_bool()
        };

        artifacts.set_expression_type(self, result_type);

        Ok(())
    }
}

fn report_redundant_isset<A>(
    context: &mut Context<'_, '_, A>,
    block_context: &BlockContext<'_>,
    artifacts: &AnalysisArtifacts,
    value: &Expression<'_>,
) where
    A: Arena,
{
    if artifacts.get_expression_type(value).is_none_or(|value_type| value_type.is_never() || value_type.has_nullish()) {
        return;
    }

    let Some(subject) = describe_always_set(context, block_context, artifacts, value) else {
        return;
    };

    if matches!(unwrap_expression(value), Expression::Access(Access::Property(_))) {
        context.collector.confirm_code_at(IssueCode::RedundantIssetCheck.as_str(), value.span());
    }

    context.collector.report_with_code(
        IssueCode::RedundantIssetCheck,
        Issue::warning(format!("{subject} is not nullable."))
            .with_annotation(Annotation::primary(value.span()).with_message("This is always set and never `null`"))
            .with_help("Remove the `isset()` check or compare the value directly."),
    );
}

fn describe_always_set<A>(
    context: &Context<'_, '_, A>,
    block_context: &BlockContext<'_>,
    artifacts: &AnalysisArtifacts,
    expression: &Expression<'_>,
) -> Option<String>
where
    A: Arena,
{
    let expression_type = known_type(context, block_context, artifacts, expression)?;
    if expression_type.possibly_undefined() || expression_type.possibly_undefined_from_try() {
        return None;
    }

    match unwrap_expression(expression) {
        Expression::Variable(Variable::Direct(variable)) => {
            let name = word(variable.name);
            if variable.name == b"$_SESSION"
                || block_context.static_locals.contains(&name)
                || !block_context.locals.contains_key(&name)
            {
                return None;
            }

            Some(format!("Variable `{name}` in `isset()` always exists and"))
        }
        Expression::ArrayAccess(access) => {
            let array_type = known_type(context, block_context, artifacts, access.array)?;
            let index_type = known_type(context, block_context, artifacts, access.index)?;
            if !array_type.is_array() {
                return None;
            }

            describe_always_set(context, block_context, artifacts, access.array)?;

            Some(format!(
                "Offset `{}` on `{}` in `isset()` always exists and",
                index_type.get_id(),
                array_type.get_id()
            ))
        }
        Expression::Access(Access::Property(access)) => {
            let ClassLikeMemberSelector::Identifier(property_identifier) = &access.property else {
                return None;
            };

            let object_type = known_type(context, block_context, artifacts, access.object)?;
            let [TAtomic::Object(object)] = object_type.types.as_ref() else {
                return None;
            };

            let class_name = object.get_name()?;
            let property_name = concat_word!(b"$", property_identifier.value);
            let property = context.codebase.get_declaring_property(class_name.as_bytes(), property_name.as_bytes())?;
            let mutable_promoted =
                property.flags.is_promoted_property() && !property.flags.is_readonly() && property.hooks.is_empty();
            if mutable_promoted {
                return None;
            }

            describe_always_set(context, block_context, artifacts, access.object)?;

            let declaring_class = context
                .codebase
                .get_declaring_property_class(class_name.as_bytes(), property_name.as_bytes())
                .and_then(|declaring_class| context.codebase.get_class_like(declaring_class.as_bytes()))?
                .original_name;

            Some(format!("Property `{declaring_class}::{property_name}` (`{}`) in `isset()`", expression_type.get_id()))
        }
        _ => None,
    }
}

fn known_type<'state, A>(
    context: &Context<'_, '_, A>,
    block_context: &'state BlockContext<'_>,
    artifacts: &'state AnalysisArtifacts,
    expression: &Expression<'_>,
) -> Option<&'state TUnion>
where
    A: Arena,
{
    artifacts.get_expression_type(expression).or_else(|| {
        get_block_expression_id(expression, context, block_context)
            .and_then(|expression_id| block_context.locals.get(&expression_id))
            .map(|local_type| local_type.as_ref())
    })
}

const fn is_valid_isset_expression(expression: &Expression) -> bool {
    match expression {
        Expression::Variable(_) | Expression::Access(_) | Expression::ArrayAccess(_) => true,
        Expression::Assignment(assignment) => assignment.operator.is_assign() && assignment.rhs.is_reference(),
        _ => false,
    }
}
