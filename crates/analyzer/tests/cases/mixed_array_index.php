<?php

/**
 * @param array<string, int> $a
 */
function read_plain(array $a, mixed $k): int
{
    // @mago-expect analysis:mixed-array-index
    return $a[$k];
}

/**
 * @param array<string, int> $a
 */
function read_coalesce(array $a, mixed $k): int
{
    // @mago-expect analysis:mixed-array-index
    return $a[$k] ?? 0;
}

/**
 * @param array<string, int> $a
 */
function read_isset(array $a, mixed $k): bool
{
    // @mago-expect analysis:mixed-array-index
    return isset($a[$k]);
}

/**
 * @param list<int> $a
 */
function read_list_coalesce(array $a, mixed $k): int
{
    // @mago-expect analysis:mixed-array-index
    return $a[$k] ?? 0;
}

/**
 * @param array<string, int> $a
 */
function read_coalesce_string_key(array $a, string $k): int
{
    return $a[$k] ?? 0;
}

/**
 * @param array<string, int> $a
 */
function read_isset_int_key(array $a, int $k): bool
{
    return isset($a[$k]);
}

/**
 * @param array<string, int> $a
 *
 * @return array<array-key, int>
 */
function write_mixed_key(array $a, mixed $k): array
{
    // @mago-expect analysis:mixed-array-index
    $a[$k] = 1;

    return $a;
}

/**
 * @param array<1, int> $a
 */
function read_wider_int_key(array $a, int $k): int
{
    return $a[$k] ?? 0;
}

/**
 * @param array{a: int} $a
 */
function read_shape_coalesce(array $a, mixed $k): int
{
    // @mago-expect analysis:mixed-array-index
    return $a[$k] ?? 0;
}

/**
 * @param array{a: int} $a
 */
function read_shape_plain(array $a, mixed $k): int
{
    // @mago-expect analysis:mixed-array-index
    return $a[$k];
}

/**
 * @param array{a: int} $a
 */
function read_shape_isset(array $a, mixed $k): bool
{
    // @mago-expect analysis:mixed-array-index
    return isset($a[$k]);
}
