<?php

declare(strict_types=1);

namespace CallableVariadicParameterCheckedOverAcceptingRest;

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

function ints(int ...$values): int
{
    return array_sum($values);
}

/** @param callable(int, string): void $_callback */
function takeIntAndString(callable $_callback): void
{
}

/** @param callable(int, string=): void $_callback */
function takeIntAndOptionalString(callable $_callback): void
{
}

/** @param callable(list<int>, list<string>): mixed $_callback */
function takeLists(callable $_callback): void
{
}

// @mago-expect analysis:invalid-argument
takeIntAndString(static function (int ...$_values): void {});
// @mago-expect analysis:invalid-argument
takeIntAndOptionalString(static function (int ...$_values): void {});
// @mago-expect analysis:possibly-invalid-argument
takeIntAndString('CallableVariadicParameterCheckedOverAcceptingRest\ints');
takeIntAndString(static function (int|string ...$_values): void {});
takeIntAndString(static function (int $_first, string ...$_rest): void {});
takeIntAndString(static function (mixed ...$_values): void {});
takeLists('CallableVariadicParameterCheckedOverAcceptingRest\merge');
takeLists('array_merge');

/** @param list<list<int>> $lists */
function flatten(array $lists): array
{
    return array_reduce($lists, 'array_merge', []);
}
