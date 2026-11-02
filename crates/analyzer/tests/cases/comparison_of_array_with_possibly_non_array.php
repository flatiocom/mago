<?php

declare(strict_types=1);

namespace ComparisonOfArrayWithPossiblyNonArray;

/**
 * @param list<int>|int $left
 * @param list<int> $right
 */
function less_than(array|int $left, array $right): bool
{
    // @mago-expect analysis:possibly-invalid-operand
    return $left < $right;
}

/**
 * @param list<int> $left
 * @param list<int>|string $right
 */
function greater_or_equal(array $left, array|string $right): bool
{
    // @mago-expect analysis:possibly-invalid-operand
    return $left >= $right;
}

/**
 * @param list<int>|int $left
 * @param list<int> $right
 */
function spaceship(array|int $left, array $right): int
{
    // @mago-expect analysis:possibly-invalid-operand
    return $left <=> $right;
}

/**
 * @param list<int> $left
 * @param list<int>|\stdClass $right
 */
function spaceship_with_object(array $left, array|\stdClass $right): int
{
    // @mago-expect analysis:possibly-invalid-operand
    return $left <=> $right;
}

/**
 * @param list<int>|int $left
 * @param list<int>|int $right
 */
function both_possibly_arrays(array|int $left, array|int $right): bool
{
    return $left < $right;
}

/**
 * @param list<int> $left
 * @param list<int> $right
 */
function arrays(array $left, array $right): bool
{
    return $left < $right;
}
