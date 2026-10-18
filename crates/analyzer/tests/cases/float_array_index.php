<?php

/**
 * @param list<string> $units
 */
function unit_of_list(array $units, float $factor): string
{
    // @mago-expect analysis:invalid-array-index
    return $units[$factor] ?? '';
}

/**
 * @param array<int, string> $units
 */
function unit_of_int_keyed(array $units, float $factor): string
{
    // @mago-expect analysis:invalid-array-index
    return $units[$factor] ?? '';
}

/**
 * @param array<int, string> $units
 */
function unit_of_int_or_float(array $units, int|float $factor): string
{
    // @mago-expect analysis:mismatched-array-index
    return $units[$factor] ?? '';
}

/**
 * @param array<int, string> $units
 */
function unit_of_int(array $units, int $factor): string
{
    return $units[$factor] ?? '';
}
