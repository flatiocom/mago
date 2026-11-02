<?php

declare(strict_types=1);

namespace UnpackedStringKeysIntoNoNamedArgumentsTarget;

/** @no-named-arguments */
function plain(int ...$numbers): int
{
    return count($numbers);
}

/** @no-named-arguments */
function one(int $number): int
{
    return $number;
}

function named(int ...$numbers): int
{
    return count($numbers);
}

final class Holder
{
    /** @no-named-arguments */
    public function take(int ...$numbers): int
    {
        return count($numbers);
    }
}

/**
 * @template K of int
 * @template S of string
 *
 * @param array<string, int> $strings
 * @param array<int|string, int> $mixedKeys
 * @param list<int> $list
 * @param array<int, int> $integers
 * @param iterable<string, int> $iterable
 * @param array<K, int> $templatedIntegers
 * @param array<S, int> $templatedStrings
 */
function run(
    Holder $holder,
    array $strings,
    array $mixedKeys,
    array $list,
    array $integers,
    iterable $iterable,
    array $templatedIntegers,
    array $templatedStrings,
): void {
    // @mago-expect analysis:named-argument-not-allowed
    plain(...$strings);
    // @mago-expect analysis:named-argument-not-allowed
    plain(...$mixedKeys);
    // @mago-expect analysis:named-argument-not-allowed
    plain(...$iterable);
    // @mago-expect analysis:named-argument-not-allowed
    plain(...['a' => 1]);
    // @mago-expect analysis:named-argument-not-allowed
    $holder->take(...$strings);
    // @mago-expect analysis:named-argument-not-allowed
    plain(...$templatedStrings);
    plain(...$list);
    plain(...$integers);
    plain(...[1, 2]);
    plain(...$templatedIntegers);
    named(...$strings);
    one(1, ...$strings);
    // @mago-expect analysis:named-argument-not-allowed
    call_user_func('UnpackedStringKeysIntoNoNamedArgumentsTarget\plain', ...$strings);
    // @mago-expect analysis:named-argument-not-allowed
    call_user_func('UnpackedStringKeysIntoNoNamedArgumentsTarget\plain', ...$mixedKeys);
    // @mago-expect analysis:named-argument-not-allowed
    call_user_func_array('UnpackedStringKeysIntoNoNamedArgumentsTarget\plain', [...$strings]);
    // @mago-expect analysis:named-argument-not-allowed
    call_user_func([$holder, 'take'], ...$strings);
    call_user_func('UnpackedStringKeysIntoNoNamedArgumentsTarget\plain', ...$list);
    call_user_func('UnpackedStringKeysIntoNoNamedArgumentsTarget\named', ...$strings);
}
