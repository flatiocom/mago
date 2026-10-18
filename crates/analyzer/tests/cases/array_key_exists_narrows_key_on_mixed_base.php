<?php

declare(strict_types=1);

/**
 * @param array<string, int> $a
 */
function read_with_key_from_mixed_base(array $a, mixed $d): int
{
    // @mago-expect analysis:mixed-array-access
    // @mago-expect analysis:mixed-argument
    if (array_key_exists($d['k'], $a)) {
        return $a[$d['k']];
    }

    return 0;
}

/**
 * @param array<string, int> $a
 */
function read_after_negated_check(array $a, mixed $d): int
{
    // @mago-expect analysis:mixed-array-access
    // @mago-expect analysis:mixed-argument
    if (!array_key_exists($d['k'], $a)) {
        return 0;
    }

    return $a[$d['k']];
}

/**
 * @param array<string, int> $a
 */
function key_takes_the_array_key_type(array $a, mixed $d): string
{
    // @mago-expect analysis:mixed-array-access
    // @mago-expect analysis:mixed-argument
    if (array_key_exists($d['k'], $a)) {
        return $d['k'];
    }

    return '';
}
