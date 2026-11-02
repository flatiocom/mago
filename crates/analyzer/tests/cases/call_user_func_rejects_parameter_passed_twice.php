<?php

declare(strict_types=1);

namespace CallUserFuncRejectsParameterPassedTwice;

function run(): void
{
    // @mago-expect analysis:named-argument-overrides-positional
    call_user_func_array(static fn (int $number): int => $number, [1, 'number' => 2]);
    // @mago-expect analysis:named-argument-overrides-positional
    call_user_func(static fn (int $number): int => $number, 1, number: 2);
    // @mago-expect analysis:duplicate-named-argument
    call_user_func(static fn (int $number): int => $number, ...['number' => 1], number: 2);
    call_user_func_array(static fn (int $number, int $other): int => $number + $other, [1, 'other' => 2]);
    call_user_func(static fn (int ...$numbers): int => count($numbers), 1, numbers: 2);
}
