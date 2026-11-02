<?php

declare(strict_types=1);

namespace CallableTemplateParameterAcceptsItsConstraint;

/**
 * @template K of array-key
 * @template V
 * @param array<K, V> ...$arrays
 * @return array<K, V>
 */
function merge(array ...$arrays): array
{
    return array_merge(...$arrays);
}

/**
 * @template T of int
 * @param T $value
 * @return T
 */
function same(int $value): int
{
    return $value;
}

/** @param callable(list<int>): mixed $_callback */
function takeList(callable $_callback): void
{
}

/** @param callable(int): mixed $_callback */
function takeInt(callable $_callback): void
{
}

/** @param callable(string): mixed $_callback */
function takeString(callable $_callback): void
{
}

takeList('CallableTemplateParameterAcceptsItsConstraint\merge');
takeList('array_merge');
takeInt('CallableTemplateParameterAcceptsItsConstraint\same');
// @mago-expect analysis:possibly-invalid-argument
takeString('CallableTemplateParameterAcceptsItsConstraint\same');
