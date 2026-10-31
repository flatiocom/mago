<?php

declare(strict_types=1);

namespace ComparisonWithAGeneralBoolKeepsOffsetRead;

/**
 * @param array<int, bool> $flags
 */
function not_identical_to_bool(array $flags, int $key, bool $expected): void
{
    // @mago-expect analysis:possibly-undefined-int-array-index
    if ($flags[$key] !== $expected) {
        // @mago-expect analysis:possibly-undefined-int-array-index
        echo $flags[$key] ? 1 : 0;
    }
}

/**
 * @param array<int, bool> $flags
 */
function not_identical_to_true(array $flags, int $key): void
{
    // @mago-expect analysis:possibly-undefined-int-array-index
    if ($flags[$key] !== true) {
        var_dump($flags[$key]);
    }
}
