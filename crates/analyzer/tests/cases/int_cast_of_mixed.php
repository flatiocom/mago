<?php

declare(strict_types=1);

function castMixed(mixed $value): int
{
    // @mago-expect analysis:invalid-type-cast
    return (int) $value;
}

function castNumeric(mixed $value): int
{
    if (!is_numeric($value)) {
        return 0;
    }

    return (int) $value;
}

function castInt(mixed $value): int
{
    if (!is_int($value)) {
        return 0;
    }

    // @mago-expect analysis:redundant-cast
    return (int) $value;
}

function castString(string $value): int
{
    return (int) $value;
}
