<?php

declare(strict_types=1);

namespace CallUserFuncCountsArgumentsAgainstCallback;

final class Joiner
{
    public function join(int $number, string $text): string
    {
        return $number . $text;
    }

    public function optional(int $number, string $text = ''): string
    {
        return $number . $text;
    }
}

function rest(): int
{
    return count(func_get_args());
}

/**
 * @param callable(int): int $callback
 */
function run(Joiner $joiner, callable $callback): void
{
    // @mago-expect analysis:too-few-arguments
    call_user_func(static fn (int $number): int => $number);
    // @mago-expect analysis:too-many-arguments
    call_user_func(static fn (int $number): int => $number, 1, 2);
    call_user_func(static fn (int $number, int $other = 0): int => $number + $other, 1);
    call_user_func(static fn (int ...$numbers): int => count($numbers), 1, 2, 3);
    // @mago-expect analysis:too-few-arguments
    call_user_func(static fn (int $number, int ...$numbers): int => $number + count($numbers));
    // @mago-expect analysis:too-few-arguments
    call_user_func([$joiner, 'join'], 1);
    call_user_func([$joiner, 'optional'], 1);
    // @mago-expect analysis:too-many-arguments
    call_user_func([$joiner, 'optional'], 1, 'a', 'b');
    // @mago-expect analysis:too-few-arguments
    call_user_func('str_repeat', 'a');
    // @mago-expect analysis:too-many-arguments
    call_user_func('strtoupper', 'a', 'b');
    // @mago-expect analysis:too-many-arguments
    call_user_func($callback, 1, 2);
    call_user_func('CallUserFuncCountsArgumentsAgainstCallback\rest', 1, 2);
    // @mago-expect analysis:too-few-arguments
    call_user_func_array(static fn (int $number): int => $number, []);
    // @mago-expect analysis:too-many-arguments
    call_user_func_array(static fn (int $number): int => $number, [1, 2]);
    call_user_func_array(static fn (int $number): int => $number, [1]);
}
