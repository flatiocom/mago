<?php

declare(strict_types=1);

namespace ArrayMergeKeepsDroppedOptionalValues;

function take_string(string $value): string
{
    return $value;
}

/**
 * @param array{a?: int} $first
 * @param array<string, string> $second
 *
 * @mago-expect analysis:possibly-invalid-argument
 */
function read_dropped(array $first, array $second): string
{
    $merged = array_merge($first, $second);

    return take_string($merged['a'] ?? '');
}

/**
 * @param array{a?: string} $first
 * @param array<string, string> $second
 */
function read_dropped_string(array $first, array $second): string
{
    $merged = array_merge($first, $second);

    return take_string($merged['a'] ?? '');
}
