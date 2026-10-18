<?php

declare(strict_types=1);

function unused_import(int $x): Closure
{
    // @mago-expect analysis:unused-closure-use-variable
    return function () use ($x): int {
        return 1;
    };
}

function unused_reference_import(int $x): Closure
{
    // @mago-expect analysis:unused-closure-use-variable
    return function () use (&$x): int {
        return 1;
    };
}

function one_of_two_unused(int $x, int $y): Closure
{
    // @mago-expect analysis:unused-closure-use-variable
    return function () use ($x, $y): int {
        return $x;
    };
}

function read_in_body(int $x): Closure
{
    return function () use ($x): int {
        return $x + 1;
    };
}

function only_overwritten(int $x): Closure
{
    return function () use ($x): int {
        $x = 2;

        return $x;
    };
}

function passed_to_nested_closure(int $x): Closure
{
    return function () use ($x): Closure {
        return function () use ($x): int {
            return $x;
        };
    };
}

function read_in_arrow_function(int $x): Closure
{
    return function () use ($x): Closure {
        return fn(): int => $x;
    };
}

function read_through_compact(int $x): Closure
{
    return function () use ($x): array {
        return compact('x');
    };
}

function read_through_func_get_args(int $x): Closure
{
    return function () use ($x): array {
        return func_get_args();
    };
}

function shadowed_in_nested_closure(int $x): Closure
{
    // @mago-expect analysis:unused-closure-use-variable
    return function () use ($x): Closure {
        return function (int $x): int {
            return $x;
        };
    };
}
