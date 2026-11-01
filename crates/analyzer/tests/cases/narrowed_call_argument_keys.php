<?php

declare(strict_types=1);

namespace NarrowedCallArgumentKeys;

final class Limits
{
    private const int Length = 3;

    public function constant_and_its_value(): int
    {
        if (intval(self::Length)) {
            if (is_int(intval(3))) { // @mago-expect analysis:redundant-type-comparison
                return 1;
            }
        }

        return 0;
    }

    public function same_constant(): int
    {
        if (intval(self::Length)) {
            if (is_int(intval(self::Length))) {
                return 1;
            }
        }

        return 0;
    }
}

function float_argument(): int
{
    if (intval(1.5)) {
        if (is_int(intval(1.5))) {
            return 1;
        }
    }

    return 0;
}

function null_argument(?string $value): int
{
    if (intval($value ?? null)) {
        if (is_int(intval($value ?? null))) {
            return 1;
        }
    }

    return 0;
}

function bool_argument(): int
{
    if (intval(true)) {
        if (is_int(intval(true))) {
            return 1;
        }
    }

    return 0;
}

function ternary_argument(bool $flag): int
{
    if (intval($flag ? '1' : '2')) {
        if (is_int(intval($flag ? '1' : '2'))) {
            return 1;
        }
    }

    return 0;
}

function interpolated_argument(string $value): int
{
    if (intval("{$value}1")) {
        if (is_int(intval("{$value}1"))) {
            return 1;
        }
    }

    return 0;
}

function cast_spelled_two_ways(string $value): int
{
    if (intval((bool) $value)) {
        if (is_int(intval((boolean) $value))) {
            return 1;
        }
    }

    return 0;
}
