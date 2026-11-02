<?php

declare(strict_types=1);

namespace UnaryPlusChecksItsOperand;

function on_mixed(mixed $value): mixed
{
    // @mago-expect analysis:mixed-operand
    return +$value;
}

function on_string(string $value): mixed
{
    // @mago-expect analysis:possibly-invalid-operand
    return +$value;
}

function on_object(\stdClass $value): mixed
{
    // @mago-expect analysis:invalid-operand
    return +$value;
}

/** @param list<int> $values */
function on_array(array $values): mixed
{
    // @mago-expect analysis:invalid-operand
    return +$values;
}

function on_non_numeric_literal(): mixed
{
    // @mago-expect analysis:invalid-operand
    return +'abc';
}

function on_int(int $value): int
{
    return +$value;
}

function on_float(float $value): float
{
    return +$value;
}

/** @param numeric-string $value */
function on_numeric_string(string $value): int|float
{
    return +$value;
}

function on_nullable_bool(?bool $value): mixed
{
    return +$value;
}
