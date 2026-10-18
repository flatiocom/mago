<?php

declare(strict_types=1);

namespace Fixture;

function native(int $i): int
{
    // @mago-expect analysis:redundant-cast
    return (int) $i;
}

/**
 * @param positive-int $i
 */
function positive(int $i): int
{
    // @mago-expect analysis:redundant-cast
    return (int) $i;
}

/**
 * @param int<min, -1>|int<1, max> $i
 */
function nonZero(mixed $i): int
{
    // @mago-expect analysis:redundant-cast
    return (int) $i;
}

function numeric(int|float $n): int
{
    return (int) $n;
}

function text(string $s): int
{
    return (int) $s;
}

function nullable(?int $i): int
{
    return (int) $i;
}
