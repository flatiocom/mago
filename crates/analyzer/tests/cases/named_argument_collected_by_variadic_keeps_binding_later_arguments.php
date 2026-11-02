<?php

declare(strict_types=1);

namespace NamedArgumentCollectedByVariadicKeepsBindingLaterArguments;

function forward(callable $callback, mixed ...$args): mixed
{
    return $callback(...$args);
}

function run(): void
{
    forward(number: 1, callback: static fn (int $number): int => $number);
    call_user_func(number: 1, callback: static fn (int $number): int => $number);
    // @mago-expect analysis:invalid-argument
    call_user_func(number: 'a', callback: static fn (int $number): int => $number);
    // @mago-expect analysis:invalid-argument
    forward(number: 1, callback: 5);
    // @mago-expect analysis:too-few-arguments
    forward(number: 1);
}
