<?php

function count_by_flag(bool $flag): int
{
    $counts = [true => 0, false => 0];
    // @mago-expect analysis:invalid-array-index
    $counts[$flag]++;

    // @mago-expect analysis:invalid-array-index
    return $counts[true];
}

/**
 * @return array<int, string>
 */
function names_by_flag(bool $flag): array
{
    $names = [];
    // @mago-expect analysis:invalid-array-index
    $names[$flag] = 'name';

    return $names;
}

/**
 * @return array<string, int>
 */
function counts_by_name(?string $name): array
{
    $counts = [];
    // @mago-expect analysis:possibly-null-array-index
    $counts[$name] = 1;

    return $counts;
}
