<?php

declare(strict_types=1);

namespace InArrayIntRangeValuesLikePhpstan;

/**
 * @param int<1, 3> $range
 * @param int<1, 3>|5 $union
 *
 * @mago-expect analysis:redundant-type-comparison(2)
 * @mago-expect analysis:impossible-type-comparison
 */
function bounded_ranges(int $range, int $union): void
{
    if (in_array($range, [1, 2, 3], true)) {
        echo 1;
    }

    if (in_array($union, [1, 2, 3, 5], true)) {
        echo 2;
    }

    if (in_array($range, [4, 5], true)) {
        echo 3;
    }
}

/**
 * @param int<1, 4> $wider
 * @param int<1, max> $open
 */
function possibly_missing(int $wider, int $open): void
{
    if (in_array($wider, [1, 2, 3], true)) {
        echo 1;
    }

    if (in_array($open, [1, 2, 3], true)) {
        echo 2;
    }
}
