use foldhash::HashSet;

use mago_span::HasSpan;
use mago_span::Span;
use mago_syntax::cst::DoWhile;
use mago_syntax::cst::Expression;
use mago_syntax::cst::For;
use mago_syntax::cst::ForBody;
use mago_syntax::cst::Foreach;
use mago_syntax::cst::ForeachBody;
use mago_syntax::cst::Function;
use mago_syntax::cst::If;
use mago_syntax::cst::IfBody;
use mago_syntax::cst::Match;
use mago_syntax::cst::MatchArm;
use mago_syntax::cst::Method;
use mago_syntax::cst::MethodBody;
use mago_syntax::cst::Namespace;
use mago_syntax::cst::NamespaceBody;
use mago_syntax::cst::Program;
use mago_syntax::cst::Sequence;
use mago_syntax::cst::Statement;
use mago_syntax::cst::SwitchDefaultCase;
use mago_syntax::cst::SwitchExpressionCase;
use mago_syntax::cst::Trivia;
use mago_syntax::cst::Try;
use mago_syntax::cst::TryCatchClause;
use mago_syntax::cst::TryFinallyClause;
use mago_syntax::cst::While;
use mago_syntax::cst::WhileBody;
use mago_syntax::walker::MutWalker;

/// The conditions PHPStan's `LastConditionVisitor` marks as last, where an always-true check is not reported:
/// the last `elseif` of an `if` whose `else` is missing or only throws, the last condition of the last `match`
/// arm, and the condition of an `if` without `else`, or of its last `elseif`, that a closing `throw` follows.
/// A comment after the last statement of a list other than a `case` is one more statement, a `Nop` in PHPStan's parser.
pub fn collect_last_conditions(program: &Program<'_>) -> HashSet<Span> {
    let mut last_conditions = HashSet::default();
    LastConditionCollector { trivia: program.trivia.as_slice() }.walk_program(program, &mut last_conditions);

    last_conditions
}

struct LastConditionCollector<'trivia, 'arena> {
    trivia: &'trivia [Trivia<'arena>],
}

enum ParsedStatement<'ast, 'arena> {
    Statement(&'ast Statement<'arena>),
    Nop,
}

impl<'ast, 'arena> MutWalker<'ast, 'arena, HashSet<Span>> for LastConditionCollector<'_, '_> {
    fn walk_in_if(&mut self, r#if: &'ast If<'arena>, last_conditions: &mut HashSet<Span>) {
        let (else_if_conditions, else_statements): (Vec<&Expression<'_>>, Option<Vec<ParsedStatement<'_, '_>>>) =
            match &r#if.body {
                IfBody::Statement(body) => (
                    body.else_if_clauses.iter().map(|clause| clause.condition).collect(),
                    body.else_clause.as_ref().map(|clause| self.body_statements(clause.statement)),
                ),
                IfBody::ColonDelimited(body) => (
                    body.else_if_clauses.iter().map(|clause| clause.condition).collect(),
                    body.else_clause.as_ref().map(|clause| self.statements(&clause.statements)),
                ),
            };

        if let Some(last_else_if_condition) = else_if_conditions.last()
            && else_statements.is_none_or(|statements| {
                matches!(statements.as_slice(), [ParsedStatement::Statement(statement)] if is_throw(statement))
            })
        {
            mark(last_conditions, last_else_if_condition);
        }

        match &r#if.body {
            IfBody::Statement(body) => {
                mark_trailing_if(last_conditions, &self.body_statements(body.statement));
                for clause in body.else_if_clauses.iter() {
                    mark_trailing_if(last_conditions, &self.body_statements(clause.statement));
                }

                if let Some(clause) = &body.else_clause {
                    mark_trailing_if(last_conditions, &self.body_statements(clause.statement));
                }
            }
            IfBody::ColonDelimited(body) => {
                mark_trailing_if(last_conditions, &self.statements(&body.statements));
                for clause in body.else_if_clauses.iter() {
                    mark_trailing_if(last_conditions, &self.statements(&clause.statements));
                }

                if let Some(clause) = &body.else_clause {
                    mark_trailing_if(last_conditions, &self.statements(&clause.statements));
                }
            }
        }
    }

    fn walk_in_match(&mut self, r#match: &'ast Match<'arena>, last_conditions: &mut HashSet<Span>) {
        if let Some(MatchArm::Expression(arm)) = r#match.arms.iter().last()
            && let Some(condition) = arm.conditions.iter().last()
        {
            mark(last_conditions, condition);
        }
    }

    fn walk_in_function(&mut self, function: &'ast Function<'arena>, last_conditions: &mut HashSet<Span>) {
        mark_trailing_if(last_conditions, &self.statements(&function.body.statements));
    }

    fn walk_in_method(&mut self, method: &'ast Method<'arena>, last_conditions: &mut HashSet<Span>) {
        if let MethodBody::Concrete(block) = &method.body {
            mark_trailing_if(last_conditions, &self.statements(&block.statements));
        }
    }

    fn walk_in_namespace(&mut self, namespace: &'ast Namespace<'arena>, last_conditions: &mut HashSet<Span>) {
        match &namespace.body {
            NamespaceBody::Implicit(body) => mark_trailing_if(last_conditions, &self.statements(&body.statements)),
            NamespaceBody::BraceDelimited(block) => {
                mark_trailing_if(last_conditions, &self.statements(&block.statements));
            }
        }
    }

    fn walk_in_switch_expression_case(
        &mut self,
        case: &'ast SwitchExpressionCase<'arena>,
        last_conditions: &mut HashSet<Span>,
    ) {
        mark_trailing_if(last_conditions, &case_statements(&case.statements));
    }

    fn walk_in_switch_default_case(
        &mut self,
        case: &'ast SwitchDefaultCase<'arena>,
        last_conditions: &mut HashSet<Span>,
    ) {
        mark_trailing_if(last_conditions, &case_statements(&case.statements));
    }

    fn walk_in_try(&mut self, r#try: &'ast Try<'arena>, last_conditions: &mut HashSet<Span>) {
        mark_trailing_if(last_conditions, &self.statements(&r#try.block.statements));
    }

    fn walk_in_try_catch_clause(&mut self, clause: &'ast TryCatchClause<'arena>, last_conditions: &mut HashSet<Span>) {
        mark_trailing_if(last_conditions, &self.statements(&clause.block.statements));
    }

    fn walk_in_try_finally_clause(
        &mut self,
        clause: &'ast TryFinallyClause<'arena>,
        last_conditions: &mut HashSet<Span>,
    ) {
        mark_trailing_if(last_conditions, &self.statements(&clause.block.statements));
    }

    fn walk_in_do_while(&mut self, do_while: &'ast DoWhile<'arena>, last_conditions: &mut HashSet<Span>) {
        mark_trailing_if(last_conditions, &self.body_statements(do_while.statement));
    }

    fn walk_in_while(&mut self, r#while: &'ast While<'arena>, last_conditions: &mut HashSet<Span>) {
        match &r#while.body {
            WhileBody::Statement(statement) => mark_trailing_if(last_conditions, &self.body_statements(statement)),
            WhileBody::ColonDelimited(body) => mark_trailing_if(last_conditions, &self.statements(&body.statements)),
        }
    }

    fn walk_in_for(&mut self, r#for: &'ast For<'arena>, last_conditions: &mut HashSet<Span>) {
        match &r#for.body {
            ForBody::Statement(statement) => mark_trailing_if(last_conditions, &self.body_statements(statement)),
            ForBody::ColonDelimited(body) => mark_trailing_if(last_conditions, &self.statements(&body.statements)),
        }
    }

    fn walk_in_foreach(&mut self, foreach: &'ast Foreach<'arena>, last_conditions: &mut HashSet<Span>) {
        match &foreach.body {
            ForeachBody::Statement(statement) => mark_trailing_if(last_conditions, &self.body_statements(statement)),
            ForeachBody::ColonDelimited(body) => {
                mark_trailing_if(last_conditions, &self.statements(&body.statements));
            }
        }
    }
}

impl LastConditionCollector<'_, '_> {
    /// The statements PHPStan's parser keeps for a body: those of a block, or the single statement.
    fn body_statements<'ast, 'arena>(&self, statement: &'ast Statement<'arena>) -> Vec<ParsedStatement<'ast, 'arena>> {
        match statement {
            Statement::Block(block) => self.statements(&block.statements),
            statement => vec![ParsedStatement::Statement(statement)],
        }
    }

    fn statements<'ast, 'arena>(
        &self,
        statements: &'ast Sequence<'arena, Statement<'arena>>,
    ) -> Vec<ParsedStatement<'ast, 'arena>> {
        let mut parsed: Vec<_> = statements.iter().map(ParsedStatement::Statement).collect();
        if statements.last().is_some_and(|statement| self.is_followed_by_comment(statement)) {
            parsed.push(ParsedStatement::Nop);
        }

        parsed
    }

    fn is_followed_by_comment(&self, statement: &Statement<'_>) -> bool {
        let mut offset = statement.span().end.offset;
        let following = self.trivia.partition_point(|trivia| trivia.span.start.offset < offset);
        for trivia in &self.trivia[following..] {
            if trivia.span.start.offset != offset {
                return false;
            }

            if trivia.kind.is_comment() {
                return true;
            }

            offset = trivia.span.end.offset;
        }

        false
    }
}

/// The statements of a `case`: PHPStan's parser keeps a comment after the last one with the next `case`.
fn case_statements<'ast, 'arena>(
    statements: &'ast Sequence<'arena, Statement<'arena>>,
) -> Vec<ParsedStatement<'ast, 'arena>> {
    statements.iter().map(ParsedStatement::Statement).collect()
}

fn mark_trailing_if(last_conditions: &mut HashSet<Span>, statements: &[ParsedStatement<'_, '_>]) {
    let [.., ParsedStatement::Statement(Statement::If(r#if)), ParsedStatement::Statement(last)] = statements else {
        return;
    };

    if !is_throw(last) {
        return;
    }

    let condition = match &r#if.body {
        IfBody::Statement(body) if body.else_clause.is_none() => {
            body.else_if_clauses.iter().last().map_or(r#if.condition, |clause| clause.condition)
        }
        IfBody::ColonDelimited(body) if body.else_clause.is_none() => {
            body.else_if_clauses.iter().last().map_or(r#if.condition, |clause| clause.condition)
        }
        _ => return,
    };

    mark(last_conditions, condition);
}

fn is_throw(statement: &Statement<'_>) -> bool {
    matches!(statement, Statement::Expression(expression) if matches!(expression.expression, Expression::Throw(_)))
}

fn mark(last_conditions: &mut HashSet<Span>, condition: &Expression<'_>) {
    last_conditions.insert(condition.unparenthesized().span());
}
