<?php

declare(strict_types=1);

namespace PowOperandsLikePhp;

/**
 * @param numeric-string $numeric
 */
function numbers(int $int, float $float, string $numeric, bool $flag, ?int $maybe): int|float
{
    return pow($int, 2) + pow($float, 2) + pow($numeric, 2) + pow($flag, 2) + pow($maybe, 2) + pow(2, $numeric);
}

/**
 * @param non-empty-string $word
 *
 * @mago-expect analysis:possibly-invalid-argument
 */
function word(string $word): int|float
{
    return pow($word, 2);
}

/**
 * @mago-expect analysis:invalid-argument
 */
function array_base(): int|float
{
    return pow([], 2);
}
