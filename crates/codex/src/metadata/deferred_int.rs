use mago_syntax::cst::BinaryOperator;

use crate::metadata::CodebaseMetadata;
use crate::ttype::atomic::TAtomic;
use crate::ttype::expander::TypeExpansionOptions;
use crate::ttype::expander::expand_union;
use crate::ttype::get_int;
use crate::ttype::get_int_or_float;
use crate::ttype::get_literal_int;
use crate::ttype::union::TUnion;

#[derive(Clone, Copy, Debug, PartialEq, Eq, Hash)]
#[cfg_attr(feature = "serde", derive(serde::Serialize, serde::Deserialize))]
pub enum DeferredIntOperator {
    BitwiseAnd,
    BitwiseOr,
    BitwiseXor,
    LeftShift,
    RightShift,
    Addition,
    Subtraction,
    Multiplication,
    Division,
    Modulo,
    Exponentiation,
}

impl DeferredIntOperator {
    #[must_use]
    pub fn from_binary(operator: &BinaryOperator<'_>) -> Option<Self> {
        Some(match operator {
            BinaryOperator::BitwiseAnd(_) => Self::BitwiseAnd,
            BinaryOperator::BitwiseOr(_) => Self::BitwiseOr,
            BinaryOperator::BitwiseXor(_) => Self::BitwiseXor,
            BinaryOperator::LeftShift(_) => Self::LeftShift,
            BinaryOperator::RightShift(_) => Self::RightShift,
            BinaryOperator::Addition(_) => Self::Addition,
            BinaryOperator::Subtraction(_) => Self::Subtraction,
            BinaryOperator::Multiplication(_) => Self::Multiplication,
            BinaryOperator::Division(_) => Self::Division,
            BinaryOperator::Modulo(_) => Self::Modulo,
            BinaryOperator::Exponentiation(_) => Self::Exponentiation,
            _ => return None,
        })
    }

    #[must_use]
    pub const fn is_bitwise(self) -> bool {
        matches!(self, Self::BitwiseAnd | Self::BitwiseOr | Self::BitwiseXor | Self::LeftShift | Self::RightShift)
    }

    #[must_use]
    pub fn apply(self, lhs: i64, rhs: i64) -> Option<i64> {
        match self {
            Self::BitwiseAnd => Some(lhs & rhs),
            Self::BitwiseOr => Some(lhs | rhs),
            Self::BitwiseXor => Some(lhs ^ rhs),
            Self::LeftShift if rhs >= 0 => {
                Some(u32::try_from(rhs).ok().and_then(|shift| lhs.checked_shl(shift)).unwrap_or_default())
            }
            Self::RightShift if rhs >= 0 => {
                let sign_fill = if lhs >= 0 { 0 } else { -1 };

                Some(u32::try_from(rhs).ok().and_then(|shift| lhs.checked_shr(shift)).unwrap_or(sign_fill))
            }
            Self::LeftShift | Self::RightShift => None,
            Self::Addition => lhs.checked_add(rhs),
            Self::Subtraction => lhs.checked_sub(rhs),
            Self::Multiplication => lhs.checked_mul(rhs),
            Self::Modulo => lhs.checked_rem(rhs),
            Self::Exponentiation => u32::try_from(rhs).ok().and_then(|exponent| lhs.checked_pow(exponent)),
            Self::Division => {
                lhs.checked_rem(rhs).filter(|remainder| *remainder == 0).and_then(|_| lhs.checked_div(rhs))
            }
        }
    }

    #[must_use]
    pub fn get_result_type(self, lhs: i64, rhs: i64) -> TUnion {
        match self.apply(lhs, rhs) {
            Some(value) => get_literal_int(value),
            None => self.get_unknown_result_type(),
        }
    }

    #[must_use]
    pub fn get_unknown_result_type(self) -> TUnion {
        if self.is_bitwise() { get_int() } else { get_int_or_float() }
    }
}

#[derive(Clone, Debug, PartialEq, Eq, Hash)]
#[cfg_attr(feature = "serde", derive(serde::Serialize, serde::Deserialize))]
pub enum DeferredIntExpression {
    Operand(TUnion),
    Operation(DeferredIntOperator, Box<DeferredIntExpression>, Box<DeferredIntExpression>),
}

impl DeferredIntExpression {
    #[must_use]
    pub fn has_reference(&self) -> bool {
        match self {
            Self::Operand(operand) => operand.types.iter().any(|atomic| matches!(atomic, TAtomic::Reference(_))),
            Self::Operation(_, lhs, rhs) => lhs.has_reference() || rhs.has_reference(),
        }
    }

    pub fn get_operands_mut(&mut self) -> Vec<&mut TUnion> {
        match self {
            Self::Operand(operand) => vec![operand],
            Self::Operation(_, lhs, rhs) => {
                let mut operands = lhs.get_operands_mut();
                operands.extend(rhs.get_operands_mut());
                operands
            }
        }
    }

    #[must_use]
    pub fn get_operator(&self) -> Option<DeferredIntOperator> {
        match self {
            Self::Operand(_) => None,
            Self::Operation(operator, _, _) => Some(*operator),
        }
    }

    #[must_use]
    pub fn evaluate(&self, codebase: &CodebaseMetadata, options: &TypeExpansionOptions) -> Option<i64> {
        match self {
            Self::Operand(operand) => {
                let mut operand = operand.clone();
                expand_union(codebase, &mut operand, options);

                operand.get_single_literal_int_value()
            }
            Self::Operation(operator, lhs, rhs) => {
                operator.apply(lhs.evaluate(codebase, options)?, rhs.evaluate(codebase, options)?)
            }
        }
    }

    #[must_use]
    pub fn resolve(&self, codebase: &CodebaseMetadata, options: &TypeExpansionOptions) -> TUnion {
        match self.evaluate(codebase, options) {
            Some(value) => get_literal_int(value),
            None => self.get_operator().map_or_else(get_int, DeferredIntOperator::get_unknown_result_type),
        }
    }

    #[must_use]
    pub fn resolve_atomic(&self, codebase: &CodebaseMetadata, options: &TypeExpansionOptions) -> TAtomic {
        self.resolve(codebase, options).get_single_owned()
    }
}
