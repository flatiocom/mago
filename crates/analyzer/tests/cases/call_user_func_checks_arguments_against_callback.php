<?php

declare(strict_types=1);

namespace CallUserFuncChecksArgumentsAgainstCallback;

final class Joiner
{
    public function join(int $number, string $text): string
    {
        return $number . $text;
    }

    public function sum(int ...$numbers): int
    {
        return array_sum($numbers);
    }
}

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

function run(Joiner $joiner, callable $any, string $text): void
{
    // @mago-expect analysis:invalid-argument
    call_user_func(static fn (int $number): int => $number, 'a');
    call_user_func(static fn (int $number): int => $number, 1);
    call_user_func([$joiner, 'join'], 1, 'a');
    // @mago-expect analysis:invalid-argument
    call_user_func([$joiner, 'join'], 1, []);
    // @mago-expect analysis:invalid-argument
    call_user_func([$joiner, 'sum'], 1, 2, 'c');
    call_user_func('strtoupper', $text);
    // @mago-expect analysis:invalid-argument
    call_user_func('strtoupper', []);
    call_user_func('CallUserFuncChecksArgumentsAgainstCallback\merge', [1], ['a']);
    call_user_func('array_merge', [1], ['a']);
    call_user_func($any, 'a', 1);
    call_user_func(static fn (int $number): int => $number, ...[1]);
}
