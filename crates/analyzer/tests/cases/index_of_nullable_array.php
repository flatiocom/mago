<?php

/**
 * @param array<string, string>|null $files
 */
function file_of(?array $files, mixed $type): ?string
{
    // @mago-expect analysis:mixed-array-index
    return $files[$type] ?? null;
}

/**
 * @param array<string, string>|null $files
 */
function file_of_name(?array $files, string $type): ?string
{
    return $files[$type] ?? null;
}

/**
 * @return array<string, array<array-key, int>>
 */
function counts_by_period(mixed $type): array
{
    $counts = [];
    // @mago-expect analysis:mixed-array-index
    $counts['month'][$type] = 1;
    // @mago-expect analysis:mixed-array-index
    $counts['year'][$type] = 2;

    return $counts;
}

/**
 * @return array<string, array<string, array<array-key, array<string, int>>>>
 */
function counts_by_template(mixed $type): array
{
    $counts = [];
    // @mago-expect analysis:mixed-array-index
    $counts['each']['month'][$type]['a'] = 1;
    // @mago-expect analysis:mixed-array-index
    $counts['each']['year'][$type]['a'] = 2;
    // @mago-expect analysis:mixed-array-index
    $counts['each']['month'][$type]['b'] = 3;

    return $counts;
}
