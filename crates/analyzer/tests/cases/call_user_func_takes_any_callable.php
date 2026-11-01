<?php

declare(strict_types=1);

namespace CallUserFuncTakesAnyCallable;

final class Formatter
{
    public function join(int $number, string $suffix): string
    {
        return $number . $suffix;
    }
}

function test_arguments_of_different_types(Formatter $formatter): string
{
    return call_user_func([$formatter, 'join'], 1, 'st');
}

function test_closure_with_parameters_of_different_types(): string
{
    return call_user_func_array(static fn(int $number, string $suffix): string => $number . $suffix, [1, 'st']);
}

function test_return_type_of_the_callback(): int
{
    return forward_static_call(static fn(int $value): int => $value, 1);
}

function test_shutdown_callback_with_parameters(): void
{
    register_shutdown_function(static function (int $_code, string $_message): void {}, 1, 'done');
}

function test_tick_callback_is_called_without_arguments(): void
{
    // @mago-expect analysis:invalid-argument
    register_tick_function(static function (int $_tick): void {}, 1);
}
