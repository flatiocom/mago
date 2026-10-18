use mago_allocator::Arena;
use mago_word::Word;
use mago_word::WordSet;
use mago_word::concat_word;
use mago_word::word;

use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::atomic::object::TObject;
use mago_reporting::Annotation;
use mago_reporting::Issue;
use mago_span::HasSpan;
use mago_syntax::cst::AnonymousClass;
use mago_syntax::cst::ArrowFunction;
use mago_syntax::cst::Assignment;
use mago_syntax::cst::AssignmentOperator;
use mago_syntax::cst::Binary;
use mago_syntax::cst::BinaryOperator;
use mago_syntax::cst::Class;
use mago_syntax::cst::ClassLikeMemberSelector;
use mago_syntax::cst::Closure;
use mago_syntax::cst::Echo;
use mago_syntax::cst::EchoTag;
use mago_syntax::cst::Enum;
use mago_syntax::cst::EvalConstruct;
use mago_syntax::cst::Expression;
use mago_syntax::cst::Foreach;
use mago_syntax::cst::Function;
use mago_syntax::cst::FunctionCall;
use mago_syntax::cst::IncludeConstruct;
use mago_syntax::cst::IncludeOnceConstruct;
use mago_syntax::cst::Instantiation;
use mago_syntax::cst::Interface;
use mago_syntax::cst::Match;
use mago_syntax::cst::MatchArm;
use mago_syntax::cst::MethodCall;
use mago_syntax::cst::NullSafeMethodCall;
use mago_syntax::cst::NullSafePropertyAccess;
use mago_syntax::cst::Pipe;
use mago_syntax::cst::PrintConstruct;
use mago_syntax::cst::PropertyAccess;
use mago_syntax::cst::RequireConstruct;
use mago_syntax::cst::RequireOnceConstruct;
use mago_syntax::cst::Statement;
use mago_syntax::cst::StaticMethodCall;
use mago_syntax::cst::StringPart;
use mago_syntax::cst::Throw;
use mago_syntax::cst::Trait;
use mago_syntax::cst::TryCatchClause;
use mago_syntax::cst::UnaryPrefix;
use mago_syntax::cst::UnaryPrefixOperator;
use mago_syntax::cst::Yield;
use mago_syntax::walker::Walker;

use crate::artifacts::AnalysisArtifacts;
use crate::artifacts::CallThrowPoint;
use crate::code::IssueCode;
use crate::context::Context;

const THROWABLE: &[u8] = b"Throwable";

pub(super) struct DeadCatchCheck {
    throw_points: Vec<CallThrowPoint>,
    try_can_throw_any_throwable: bool,
    caught_above: Vec<Word>,
}

impl DeadCatchCheck {
    pub(super) fn new<'arena, A>(
        statements: &[Statement<'arena>],
        context: &Context<'_, 'arena, A>,
        artifacts: &AnalysisArtifacts,
    ) -> Self
    where
        A: Arena,
    {
        let mut collector = ThrowPointCollector { throw_points: vec![], context, artifacts, throw_operand: None };
        for statement in statements {
            ThrowPointWalker.walk_statement(statement, &mut collector);
        }

        let try_can_throw_any_throwable =
            collector.throw_points.iter().any(|throw_point| matches!(throw_point, CallThrowPoint::AnyThrowable));

        Self { throw_points: collector.throw_points, try_can_throw_any_throwable, caught_above: vec![] }
    }

    pub(super) fn check_catch_clause<'arena, A>(
        &mut self,
        context: &mut Context<'_, 'arena, A>,
        catch_clause: &TryCatchClause<'arena>,
        caught_classes: &WordSet,
    ) where
        A: Arena,
    {
        let mut caught_classes = caught_classes.iter().copied().collect::<Vec<_>>();
        caught_classes.sort();

        let catches_throwable = caught_classes.iter().any(|class| class.as_bytes().eq_ignore_ascii_case(THROWABLE));

        for caught_class in &caught_classes {
            if self.caught_above.iter().any(|above| is_same_or_subclass(context, *caught_class, *above)) {
                continue;
            }

            let is_thrown = if caught_class.as_bytes().eq_ignore_ascii_case(THROWABLE) {
                !self.throw_points.is_empty() || self.try_can_throw_any_throwable
            } else {
                catches_throwable
                    || self.throw_points.iter().any(|throw_point| can_be_caught_as(context, throw_point, *caught_class))
            };

            if !is_thrown {
                report_dead_catch(context, catch_clause, *caught_class);
            }
        }

        self.throw_points.retain_mut(|throw_point| match throw_point {
            CallThrowPoint::AnyThrowable => !catches_throwable,
            CallThrowPoint::Classes(thrown_classes) => {
                thrown_classes.retain(|thrown_class| {
                    !caught_classes
                        .iter()
                        .any(|caught_class| is_same_or_subclass(context, *thrown_class, *caught_class))
                });

                !thrown_classes.is_empty()
            }
        });

        self.caught_above.extend(caught_classes);
    }
}

fn report_dead_catch<A>(context: &mut Context<'_, '_, A>, catch_clause: &TryCatchClause<'_>, caught_class: Word)
where
    A: Arena,
{
    context.collector.report_with_code(
        IssueCode::DeadCatch,
        Issue::warning(format!("Dead catch: `{caught_class}` is never thrown in the try block."))
            .with_annotation(
                Annotation::primary(catch_clause.hint.span())
                    .with_message(format!("Nothing in the try block throws `{caught_class}`")),
            )
            .with_help("Remove the catch clause, or catch an exception the try block can throw."),
    );
}

fn can_be_caught_as<A>(context: &Context<'_, '_, A>, throw_point: &CallThrowPoint, caught_class: Word) -> bool
where
    A: Arena,
{
    let CallThrowPoint::Classes(thrown_classes) = throw_point else {
        return true;
    };

    thrown_classes.iter().any(|thrown_class| classes_can_overlap(context, *thrown_class, caught_class))
}

fn classes_can_overlap<A>(context: &Context<'_, '_, A>, thrown_class: Word, caught_class: Word) -> bool
where
    A: Arena,
{
    if is_same_or_subclass(context, thrown_class, caught_class)
        || is_same_or_subclass(context, caught_class, thrown_class)
    {
        return true;
    }

    let (Some(thrown_metadata), Some(caught_metadata)) = (
        context.codebase.get_class_like(thrown_class.as_bytes()),
        context.codebase.get_class_like(caught_class.as_bytes()),
    ) else {
        return true;
    };

    (caught_metadata.kind.is_interface() && !thrown_metadata.flags.is_final())
        || (thrown_metadata.kind.is_interface() && !caught_metadata.flags.is_final())
}

fn is_same_or_subclass<A>(context: &Context<'_, '_, A>, class: Word, parent: Word) -> bool
where
    A: Arena,
{
    class.as_bytes().eq_ignore_ascii_case(parent.as_bytes())
        || context.codebase.is_instance_of(class.as_bytes(), parent.as_bytes())
}

struct ThrowPointCollector<'analysis, 'ctx, 'arena, A>
where
    A: Arena,
{
    throw_points: Vec<CallThrowPoint>,
    context: &'analysis Context<'ctx, 'arena, A>,
    artifacts: &'analysis AnalysisArtifacts,
    throw_operand: Option<(u32, u32)>,
}

impl<A> ThrowPointCollector<'_, '_, '_, A>
where
    A: Arena,
{
    fn add_any_throwable(&mut self) {
        self.throw_points.push(CallThrowPoint::AnyThrowable);
    }

    fn add_class(&mut self, class: &[u8]) {
        self.throw_points.push(CallThrowPoint::Classes(vec![word(class)]));
    }

    fn add_thrown_value(&mut self, exception: &Expression<'_>) {
        match self.get_object_classes(exception) {
            Some(classes) => self.throw_points.push(CallThrowPoint::Classes(classes)),
            None => self.add_any_throwable(),
        }
    }

    fn add_call(&mut self, call: &impl HasSpan, receiver: Option<&Expression<'_>>) {
        let receiver_is_unresolved = receiver.is_some_and(|receiver| self.get_object_classes(receiver).is_none());
        let throw_point = match self.artifacts.get_call_throw_point(call) {
            Some(throw_point) if !receiver_is_unresolved => throw_point.clone(),
            _ => CallThrowPoint::AnyThrowable,
        };

        if throw_point == CallThrowPoint::AnyThrowable && self.creates_thrown_exception(call) {
            return;
        }

        self.throw_points.push(throw_point);
    }

    fn creates_thrown_exception(&self, call: &impl HasSpan) -> bool {
        let span = call.span();
        if self.throw_operand != Some((span.start.offset, span.end.offset)) {
            return false;
        }

        self.artifacts.get_expression_type(call).is_some_and(|call_type| {
            call_type.types.iter().all(|atomic| atomic.extends_or_implements(self.context.codebase, THROWABLE))
        })
    }

    fn add_string_conversion(&mut self, expression: &Expression<'_>) {
        let can_call_to_string = self.artifacts.get_expression_type(expression).is_none_or(|expression_type| {
            expression_type.types.iter().any(|atomic| {
                !matches!(
                    atomic,
                    TAtomic::Scalar(_)
                        | TAtomic::Null
                        | TAtomic::Array(_)
                        | TAtomic::Resource(_)
                        | TAtomic::Never
                        | TAtomic::Void
                )
            })
        });

        if can_call_to_string {
            self.add_any_throwable();
        }
    }

    fn add_property_access(&mut self, object: &Expression<'_>, property: &ClassLikeMemberSelector<'_>) {
        let ClassLikeMemberSelector::Identifier(property_name) = property else {
            self.add_any_throwable();
            return;
        };

        let Some(object_classes) = self.get_object_classes(object) else {
            self.add_any_throwable();
            return;
        };

        let property_name = concat_word!(b"$", property_name.value);
        let can_have_hook = object_classes.iter().any(|class| !self.is_unhookable_property(*class, property_name));
        if can_have_hook {
            self.add_any_throwable();
        }
    }

    fn is_unhookable_property(&self, class: Word, property_name: Word) -> bool {
        let codebase = self.context.codebase;
        let Some(property) = codebase.get_declaring_property(class.as_bytes(), property_name.as_bytes()) else {
            return false;
        };

        if !property.hooks.is_empty() {
            return false;
        }

        property.is_final()
            || property.flags.is_final()
            || codebase
                .get_declaring_property_class(class.as_bytes(), property_name.as_bytes())
                .and_then(|declaring_class| codebase.get_class_like(declaring_class.as_bytes()))
                .is_some_and(|declaring_class| declaring_class.flags.is_final())
    }

    fn get_object_classes(&self, expression: &Expression<'_>) -> Option<Vec<Word>> {
        let expression_type = self.artifacts.get_expression_type(expression)?;
        let mut classes = vec![];
        for atomic in expression_type.types.iter() {
            match atomic {
                TAtomic::Object(TObject::Named(_)) => classes.extend(atomic.get_all_object_names()),
                TAtomic::Null => {}
                _ => return None,
            }
        }

        Some(classes)
    }

    fn is_array(&self, expression: &Expression<'_>) -> bool {
        self.artifacts.get_expression_type(expression).is_some_and(|expression_type| {
            expression_type.types.iter().all(|atomic| matches!(atomic, TAtomic::Array(_) | TAtomic::Never))
        })
    }
}

struct ThrowPointWalker;

impl<'ast, 'arena, A> Walker<'ast, 'arena, ThrowPointCollector<'_, '_, 'arena, A>> for ThrowPointWalker
where
    A: Arena,
{
    fn walk_in_throw(&self, throw: &'ast Throw<'arena>, collector: &mut ThrowPointCollector<'_, '_, 'arena, A>) {
        collector.add_thrown_value(throw.exception);
        let span = throw.exception.span();
        collector.throw_operand = Some((span.start.offset, span.end.offset));
    }

    fn walk_in_function_call(
        &self,
        function_call: &'ast FunctionCall<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        collector.add_call(function_call, None);
    }

    fn walk_in_method_call(
        &self,
        method_call: &'ast MethodCall<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        collector.add_call(method_call, Some(method_call.object));
    }

    fn walk_in_null_safe_method_call(
        &self,
        method_call: &'ast NullSafeMethodCall<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        collector.add_call(method_call, Some(method_call.object));
    }

    fn walk_in_static_method_call(
        &self,
        method_call: &'ast StaticMethodCall<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        collector.add_call(method_call, None);
    }

    fn walk_in_instantiation(
        &self,
        instantiation: &'ast Instantiation<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        collector.add_call(instantiation, None);
    }

    fn walk_in_property_access(
        &self,
        property_access: &'ast PropertyAccess<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        collector.add_property_access(property_access.object, &property_access.property);
    }

    fn walk_in_null_safe_property_access(
        &self,
        property_access: &'ast NullSafePropertyAccess<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        collector.add_property_access(property_access.object, &property_access.property);
    }

    fn walk_in_assignment(
        &self,
        assignment: &'ast Assignment<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        match assignment.operator {
            AssignmentOperator::Division(_) | AssignmentOperator::Modulo(_) => {
                collector.add_class(b"DivisionByZeroError");
            }
            AssignmentOperator::Concat(_) => collector.add_string_conversion(assignment.rhs),
            _ => {}
        }

        if matches!(assignment.lhs, Expression::Access(_)) {
            collector.add_class(b"TypeError");
        }
    }

    fn walk_in_binary(&self, binary: &'ast Binary<'arena>, collector: &mut ThrowPointCollector<'_, '_, 'arena, A>) {
        match binary.operator {
            BinaryOperator::Division(_) | BinaryOperator::Modulo(_) => collector.add_class(b"DivisionByZeroError"),
            BinaryOperator::StringConcat(_) => {
                collector.add_string_conversion(binary.lhs);
                collector.add_string_conversion(binary.rhs);
            }
            _ => {}
        }
    }

    fn walk_in_unary_prefix(
        &self,
        unary_prefix: &'ast UnaryPrefix<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        if let UnaryPrefixOperator::StringCast(..) = unary_prefix.operator {
            collector.add_string_conversion(unary_prefix.operand);
        }
    }

    fn walk_in_string_part(
        &self,
        string_part: &'ast StringPart<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        match string_part {
            StringPart::Expression(expression) => collector.add_string_conversion(expression),
            StringPart::BracedExpression(braced) => collector.add_string_conversion(braced.expression),
            StringPart::Literal(_) => {}
        }
    }

    fn walk_in_echo(&self, echo: &'ast Echo<'arena>, collector: &mut ThrowPointCollector<'_, '_, 'arena, A>) {
        for value in echo.values.iter() {
            collector.add_string_conversion(value);
        }
    }

    fn walk_in_echo_tag(
        &self,
        echo_tag: &'ast EchoTag<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        for value in echo_tag.values.iter() {
            collector.add_string_conversion(value);
        }
    }

    fn walk_in_print_construct(
        &self,
        print_construct: &'ast PrintConstruct<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        collector.add_string_conversion(print_construct.value);
    }

    fn walk_in_match(&self, r#match: &'ast Match<'arena>, collector: &mut ThrowPointCollector<'_, '_, 'arena, A>) {
        if !r#match.arms.iter().any(|arm| matches!(arm, MatchArm::Default(_))) {
            collector.add_class(b"UnhandledMatchError");
        }
    }

    fn walk_in_foreach(&self, foreach: &'ast Foreach<'arena>, collector: &mut ThrowPointCollector<'_, '_, 'arena, A>) {
        if !collector.is_array(foreach.expression) {
            collector.add_any_throwable();
        }
    }

    fn walk_in_yield(&self, _: &'ast Yield<'arena>, collector: &mut ThrowPointCollector<'_, '_, 'arena, A>) {
        collector.add_any_throwable();
    }

    fn walk_in_pipe(&self, _: &'ast Pipe<'arena>, collector: &mut ThrowPointCollector<'_, '_, 'arena, A>) {
        collector.add_any_throwable();
    }

    fn walk_in_eval_construct(
        &self,
        _: &'ast EvalConstruct<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        collector.add_any_throwable();
    }

    fn walk_in_include_construct(
        &self,
        _: &'ast IncludeConstruct<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        collector.add_any_throwable();
    }

    fn walk_in_include_once_construct(
        &self,
        _: &'ast IncludeOnceConstruct<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        collector.add_any_throwable();
    }

    fn walk_in_require_construct(
        &self,
        _: &'ast RequireConstruct<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        collector.add_any_throwable();
    }

    fn walk_in_require_once_construct(
        &self,
        _: &'ast RequireOnceConstruct<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        collector.add_any_throwable();
    }

    fn walk_anonymous_class(
        &self,
        _: &'ast AnonymousClass<'arena>,
        collector: &mut ThrowPointCollector<'_, '_, 'arena, A>,
    ) {
        collector.add_any_throwable();
    }

    fn walk_closure(&self, _: &'ast Closure<'arena>, _: &mut ThrowPointCollector<'_, '_, 'arena, A>) {}

    fn walk_arrow_function(&self, _: &'ast ArrowFunction<'arena>, _: &mut ThrowPointCollector<'_, '_, 'arena, A>) {}

    fn walk_function(&self, _: &'ast Function<'arena>, _: &mut ThrowPointCollector<'_, '_, 'arena, A>) {}

    fn walk_class(&self, _: &'ast Class<'arena>, _: &mut ThrowPointCollector<'_, '_, 'arena, A>) {}

    fn walk_interface(&self, _: &'ast Interface<'arena>, _: &mut ThrowPointCollector<'_, '_, 'arena, A>) {}

    fn walk_trait(&self, _: &'ast Trait<'arena>, _: &mut ThrowPointCollector<'_, '_, 'arena, A>) {}

    fn walk_enum(&self, _: &'ast Enum<'arena>, _: &mut ThrowPointCollector<'_, '_, 'arena, A>) {}
}
