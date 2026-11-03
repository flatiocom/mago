use indoc::indoc;
use mago_allocator::Arena;
use schemars::JsonSchema;

use mago_reporting::Annotation;
use mago_reporting::Issue;
use mago_reporting::Level;
use mago_span::HasSpan;
use mago_syntax::cst::Expression;
use mago_syntax::cst::Node;
use mago_syntax::cst::NodeKind;
use mago_syntax::cst::Variable;

use crate::category::Category;
use crate::context::LintContext;
use crate::requirements::RuleRequirements;
use crate::rule::Config;
use crate::rule::LintRule;
use crate::rule_meta::RuleMeta;
use crate::settings::RuleSettings;

#[derive(Debug, Clone)]
pub struct NoVariableVariableRule {
    meta: &'static RuleMeta,
    cfg: NoVariableVariableConfig,
}

#[derive(Debug, Clone, Copy, Eq, PartialEq, Hash, JsonSchema)]
#[cfg_attr(feature = "serde", derive(serde::Serialize, serde::Deserialize))]
#[cfg_attr(feature = "serde", serde(default, rename_all = "kebab-case", deny_unknown_fields))]
pub struct NoVariableVariableConfig {
    pub level: Level,
}

impl Default for NoVariableVariableConfig {
    fn default() -> Self {
        Self { level: Level::Warning }
    }
}

impl Config for NoVariableVariableConfig {
    fn level(&self) -> Level {
        self.level
    }
}

impl LintRule for NoVariableVariableRule {
    type Config = NoVariableVariableConfig;

    fn meta() -> &'static RuleMeta {
        const META: RuleMeta = RuleMeta {
            name: "No Variable Variable",
            code: "no-variable-variable",
            description: indoc! {r"
                Discourages usage of PHP's variable variables feature.

                Variable variables can make code harder to read and maintain, as they introduce a level of indirection that can confuse readers and complicate static analysis.
            "},
            good_example: indoc! {r"
                <?php

                $foo = 'bar';

                echo $foo; // Outputs 'bar'
            "},
            bad_example: indoc! {r"
                <?php

                $foo = 'bar';
                $varName = 'foo';

                echo $$varName; // Outputs 'bar'
            "},
            category: Category::Clarity,
            requirements: RuleRequirements::None,
        };

        &META
    }

    fn targets() -> &'static [NodeKind] {
        const TARGETS: &[NodeKind] = &[NodeKind::Variable];

        TARGETS
    }

    fn build(settings: &RuleSettings<Self::Config>) -> Self {
        Self { meta: Self::meta(), cfg: settings.config }
    }

    fn check<'arena, A>(&self, ctx: &mut LintContext<'_, 'arena, A>, node: Node<'_, 'arena>)
    where
        A: Arena,
    {
        let Node::Variable(variable) = node else {
            return;
        };

        let span = match variable {
            Variable::Nested(nested_variable) => nested_variable.span(),
            Variable::Indirect(indirect_variable) if !names_a_variable(indirect_variable.expression) => {
                indirect_variable.span()
            }
            _ => return,
        };

        ctx.collector.report(
            Issue::new(
                self.cfg.level,
                "Usage of variable variables (e.g., `$$var`) is discouraged.",
            )
            .with_code(self.meta.code)
            .with_annotation(
                Annotation::primary(span)
                    .with_message("This is a variable variable"),
            )
            .with_note(
                "Variable variables can make code harder to read and maintain. Consider using arrays or other data structures instead.",
            )
            .with_help("Refactor the code to avoid using variable variables."),
        );
    }
}

fn names_a_variable(expression: &Expression<'_>) -> bool {
    match expression {
        Expression::Identifier(_) => true,
        Expression::ArrayAccess(access) => matches!(access.array, Expression::Identifier(_)),
        _ => false,
    }
}

#[cfg(test)]
mod tests {
    use indoc::indoc;

    use super::NoVariableVariableRule;
    use crate::test_lint_failure;
    use crate::test_lint_success;

    test_lint_failure! {
        name = braced_variable_variables_are_reported,
        rule = NoVariableVariableRule,
        count = 4,
        code = indoc! {r#"
            <?php

            echo ${$name};
            echo ${'foo'};
            echo Ctx::${$name};
            echo "${$name}";
        "#}
    }

    test_lint_success! {
        name = interpolated_braced_name_is_not_reported,
        rule = NoVariableVariableRule,
        code = indoc! {r#"
            <?php

            echo "${name}";
        "#}
    }

    test_lint_success! {
        name = interpolated_braced_array_name_is_not_reported,
        rule = NoVariableVariableRule,
        code = indoc! {r#"
            <?php

            echo "${name['k']}";
        "#}
    }
}
