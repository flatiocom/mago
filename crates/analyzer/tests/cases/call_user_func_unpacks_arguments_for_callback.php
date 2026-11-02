<?php

declare(strict_types=1);

namespace CallUserFuncUnpacksArgumentsForCallback;

/**
 * @param list<string> $texts
 * @param list<int> $numbers
 * @param array{number: string} $named
 * @param array{number: int} $partial
 */
function run(array $texts, array $numbers, array $named, array $partial): void
{
    // @mago-expect analysis:invalid-argument
    call_user_func(static fn (int $number): int => $number, ...$texts);
    call_user_func(static fn (int $number): int => $number, ...$numbers);
    // @mago-expect analysis:invalid-argument
    call_user_func(static fn (int $number): int => $number, ...['a']);
    // @mago-expect analysis:invalid-argument
    call_user_func_array(static fn (int $number): int => $number, [...$texts]);
    call_user_func_array(static fn (int $number): int => $number, [...$numbers]);
    // @mago-expect analysis:invalid-argument
    call_user_func(static fn (int $number, int $other): int => $number + $other, 1, ...$texts);
    call_user_func(static fn (int $number, int $other): int => $number + $other, ...$numbers);
    // @mago-expect analysis:too-many-arguments
    call_user_func(static fn (int $number): int => $number, ...[1, 2]);
    // @mago-expect analysis:too-few-arguments
    call_user_func(static fn (int $number, int $other): int => $number + $other, ...[1]);
    // @mago-expect analysis:invalid-argument
    call_user_func(static fn (int $number): int => $number, ...['number' => 'a']);
    // @mago-expect analysis:invalid-argument
    call_user_func(static fn (int $number): int => $number, ...$named);
    // @mago-expect analysis:too-few-arguments
    call_user_func(static fn (int $number, int $other): int => $number + $other, ...$partial);
}
