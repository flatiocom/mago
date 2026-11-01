<?php

declare(strict_types=1);

namespace OmittedDefaultJoinsTemplateBounds;

function take_int(int $value): int
{
    return $value;
}

/**
 * @template T
 *
 * @param callable(): T $factory
 * @param T $fallback
 *
 * @return T
 */
function first(callable $factory, mixed $fallback = null): mixed
{
    return $fallback ?? $factory();
}

/**
 * @mago-expect analysis:possibly-null-argument
 */
function omitted(): int
{
    return take_int(first(static fn (): int => 1));
}

function passed(): int
{
    return take_int(first(static fn (): int => 1, 2));
}

/**
 * @param list<int> $values
 *
 * @mago-expect analysis:possibly-null-argument
 */
function reduce_without_initial(array $values): int
{
    return take_int(array_reduce($values, static fn (?int $carry, int $value): int => ($carry ?? 0) + $value));
}

/**
 * @param list<int> $values
 */
function reduce_with_initial(array $values): int
{
    return take_int(array_reduce($values, static fn (int $carry, int $value): int => $carry + $value, 0));
}
