<?php

declare(strict_types=1);

final class Inner
{
    public function name(): null|string
    {
        return rand(0, 1) ? null : 'name';
    }
}

final class Outer
{
    public function inner(): null|Inner
    {
        return rand(0, 1) ? null : new Inner();
    }
}

function after_return(null|Outer $outer): int
{
    if ($outer?->inner()?->name() === null) {
        return 0;
    }

    return strlen($outer->inner()->name());
}

function in_the_true_branch(null|Outer $outer): int
{
    if ($outer?->inner()?->name() !== null) {
        return strlen($outer->inner()->name());
    }

    return 0;
}
