<?php

declare(strict_types=1);

final class Money
{
    public function __construct(
        public int $amount,
    ) {}
}

function object_greater_than_int(stdClass $object): bool
{
    return $object > 1; // @mago-expect analysis:invalid-operand
}

function float_equal_to_object(Money $money, float $limit): bool
{
    return $limit == $money; // @mago-expect analysis:invalid-operand
}

function nullable_object_compared_with_int(?Money $money): bool
{
    return $money <= 0; // @mago-expect analysis:invalid-operand,possibly-null-operand
}

function closure_not_equal_to_int(Closure $closure): bool
{
    return $closure != 0; // @mago-expect analysis:invalid-operand
}

/**
 * @template T of object
 *
 * @param T $object
 */
function template_object_compared_with_int(object $object): bool
{
    return 5 < $object; // @mago-expect analysis:invalid-operand
}

function object_spaceship_int(Money $money): int
{
    return $money <=> 3; // @mago-expect analysis:invalid-operand
}

function gmp_compared_with_int(GMP $gmp): bool
{
    return $gmp > 1;
}

function object_compared_with_int_or_string(Money $money, int|string $value): bool
{
    return $money == $value;
}

function objects_compared(Money $left, Money $right): bool
{
    return $left > $right;
}

function number_taken_from_object(Money $money): bool
{
    return $money->amount > 0;
}
