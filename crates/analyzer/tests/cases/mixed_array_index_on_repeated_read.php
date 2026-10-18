<?php

declare(strict_types=1);

/**
 * @param array<string, int> $a
 */
function read_twice(array $a, mixed $k): int
{
    // @mago-expect analysis:mixed-array-index
    $first = $a[$k];

    // @mago-expect analysis:mixed-array-index
    return $first + $a[$k];
}

/**
 * @param array<string, int> $a
 */
function read_after_isset(array $a, mixed $k): int
{
    // @mago-expect analysis:mixed-array-index
    if (isset($a[$k])) {
        // @mago-expect analysis:mixed-array-index
        return $a[$k];
    }

    return 0;
}

/**
 * @param array<string, int> $a
 */
function read_after_coalesce(array $a, mixed $k): int
{
    // @mago-expect analysis:mixed-array-index
    $first = $a[$k] ?? 0;

    // @mago-expect analysis:mixed-array-index
    return $first + $a[$k];
}

/**
 * @param array<string, string> $a
 *
 * @return array<array-key, string>
 */
function append_after_read(array $a, mixed $k): array
{
    // @mago-expect analysis:mixed-array-index
    echo $a[$k];

    // @mago-expect analysis:mixed-array-index
    $a[$k] .= 'x';

    return $a;
}

/**
 * @param array<string, int> $a
 */
function read_twice_string_key(array $a, string $k): int
{
    if (isset($a[$k])) {
        return $a[$k] + $a[$k];
    }

    return 0;
}

/**
 * @param array{a: int} $a
 */
function read_shape_twice(array $a, mixed $k): int
{
    // @mago-expect analysis:mixed-array-index
    $first = $a[$k];

    // @mago-expect analysis:mixed-array-index
    return $first + $a[$k];
}

/**
 * @param array<string, int> $a
 */
function read_with_postfix_after_isset(array $a, mixed $k): int
{
    // @mago-expect analysis:mixed-array-index
    if (isset($a[$k])) {
        // @mago-expect analysis:mixed-array-index
        // @mago-expect analysis:mixed-assignment
        // @mago-expect analysis:mixed-operand
        return $a[$k++];
    }

    return 0;
}

/**
 * @param array<string, int> $a
 * @param list<int> $items
 */
function read_in_loop_after_isset(array $a, mixed $k, array $items): int
{
    // @mago-expect analysis:mixed-array-index
    if (!isset($a[$k])) {
        return 0;
    }

    $sum = 0;
    foreach ($items as $item) {
        // @mago-expect analysis:mixed-array-index
        $sum += $a[$k] + $item;
    }

    return $sum;
}
