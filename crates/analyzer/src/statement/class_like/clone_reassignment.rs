use mago_syntax::cst::Access;
use mago_syntax::cst::Argument;
use mago_syntax::cst::ArrayElement;
use mago_syntax::cst::Assignment;
use mago_syntax::cst::ClassLikeMember;
use mago_syntax::cst::ClassLikeMemberSelector;
use mago_syntax::cst::Expression;
use mago_syntax::cst::FunctionCall;
use mago_syntax::cst::Literal;
use mago_syntax::cst::LiteralString;
use mago_syntax::cst::Method;
use mago_syntax::cst::MethodBody;
use mago_syntax::cst::Variable;
use mago_syntax::walker::MutWalker;
use mago_word::WordSet;
use mago_word::word;

/// The properties a clone of the class may hold with another value than its original: the ones the class's
/// `__clone()` assigns on `$this` and the ones a `clone($object, [...])` in the class names.
#[derive(Default)]
pub(super) struct CloneReassignedProperties {
    names: WordSet,
    names_unknown_properties: bool,
    in_clone_method: bool,
}

impl CloneReassignedProperties {
    pub(super) fn collect(members: &[ClassLikeMember<'_>]) -> Self {
        let mut collector = Self::default();
        for member in members {
            collector.walk_class_like_member(member, &mut ());
        }

        collector
    }

    pub(super) fn contains(&self, property_name: &[u8]) -> bool {
        self.names_unknown_properties || self.names.contains(&word(property_name))
    }

    fn record_clone_with(&mut self, properties: &Expression<'_>) {
        let Expression::Array(array) = properties else {
            self.names_unknown_properties = true;
            return;
        };

        for element in array.elements.iter() {
            match element {
                ArrayElement::KeyValue(element) => match element.key {
                    Expression::Literal(Literal::String(LiteralString { value: Some(name), .. })) => {
                        self.names.insert(word(name));
                    }
                    _ => self.names_unknown_properties = true,
                },
                ArrayElement::Variadic(_) => self.names_unknown_properties = true,
                ArrayElement::Value(_) | ArrayElement::Missing(_) => {}
            }
        }
    }
}

impl<'ast, 'arena> MutWalker<'ast, 'arena, ()> for CloneReassignedProperties {
    fn walk_in_method(&mut self, method: &'ast Method<'arena>, _: &mut ()) {
        self.in_clone_method =
            method.name.value.eq_ignore_ascii_case(b"__clone") && matches!(method.body, MethodBody::Concrete(_));
    }

    fn walk_out_method(&mut self, _: &'ast Method<'arena>, _: &mut ()) {
        self.in_clone_method = false;
    }

    fn walk_in_assignment(&mut self, assignment: &'ast Assignment<'arena>, _: &mut ()) {
        if !self.in_clone_method {
            return;
        }

        if let Expression::Access(Access::Property(access)) = assignment.lhs
            && let Expression::Variable(Variable::Direct(object)) = access.object
            && object.name == b"$this"
            && let ClassLikeMemberSelector::Identifier(property) = &access.property
        {
            self.names.insert(word(property.value));
        }
    }

    fn walk_in_function_call(&mut self, function_call: &'ast FunctionCall<'arena>, _: &mut ()) {
        let Expression::Identifier(function) = function_call.function else {
            return;
        };
        if !function.value().eq_ignore_ascii_case(b"clone") {
            return;
        }

        match function_call.argument_list.arguments.get(1) {
            Some(Argument::Positional(argument)) => self.record_clone_with(argument.value),
            Some(Argument::Named(_)) => self.names_unknown_properties = true,
            None => {}
        }
    }
}
