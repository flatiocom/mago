<?php

declare(strict_types=1);

namespace ArrayReduceOverSealedArrays;

function take_int(int $value): int
{
    return $value;
}

/**
 * @param array{a: int, b?: int} $values
 */
function non_empty_shape(array $values): int
{
    return take_int(array_reduce($values, static fn (?int $carry, int $value): int => ($carry ?? 0) + $value));
}

/**
 * @mago-expect analysis:invalid-argument
 */
function empty_shape(): int
{
    return take_int(array_reduce([], static fn (?int $carry, int $value): int => ($carry ?? 0) + $value, 'x'));
}
