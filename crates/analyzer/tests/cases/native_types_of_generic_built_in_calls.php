<?php

declare(strict_types=1);

namespace NativeTypesOfGenericBuiltInCalls;

/**
 * @param list<int> $values
 *
 * @mago-expect analysis:redundant-comparison
 */
function negative_count(array $values): bool
{
    return count($values) < 0;
}

/**
 * @mago-expect analysis:redundant-comparison
 */
function upper_lowercase(string $s): bool
{
    return strtolower($s) === 'ABC';
}

/**
 * @param array<string, int> $values
 *
 * @mago-expect analysis:redundant-type-comparison
 */
function keys_are_list(array $values): bool
{
    return array_is_list(array_keys($values));
}

/**
 * @mago-expect analysis:redundant-comparison
 */
function md5_empty(string $s): bool
{
    return md5($s) === '';
}

/**
 * @param list<int> $values
 */
function sum_of_ints(array $values): int
{
    return (int) array_sum(array_map(static fn (int $value): int => $value * 2, $values));
}

function extension_of(string $name): string
{
    return (string) pathinfo($name, PATHINFO_EXTENSION);
}

/**
 * @param string|array<string> $paths
 */
function first_string(string|array $paths): string
{
    $list = is_array($paths) ? array_values($paths) : [$paths];
    foreach ($list as $path) {
        if (!is_string($path)) {
            continue;
        }

        return $path;
    }

    return '';
}
