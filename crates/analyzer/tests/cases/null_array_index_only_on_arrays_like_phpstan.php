<?php

declare(strict_types=1);

/**
 * @param array<string, int> $map
 */
function null_key_on_array(array $map, null|string $key): int
{
    /** @mago-expect analysis:possibly-null-array-index */
    return $map[$key] ?? 0;
}

/**
 * @param ArrayAccess<string, int> $table
 */
function null_key_on_array_access(ArrayAccess $table, null|string $key): mixed
{
    return $table[$key];
}

/**
 * @param ArrayAccess<string, int> $table
 */
function int_key_on_string_keyed_array_access(ArrayAccess $table, int $key): mixed
{
    /** @mago-expect analysis:invalid-array-index */
    return $table[$key];
}

/**
 * @param ArrayAccess<string, int> $table
 */
function null_key_write_on_array_access(ArrayAccess $table, null|string $key): void
{
    /** @mago-expect analysis:invalid-array-index */
    $table[$key] = 1;
}

/**
 * @param array<string, int>|ArrayAccess<string, int> $table
 */
function null_key_on_array_or_array_access(array|ArrayAccess $table, null|string $key): mixed
{
    /** @mago-expect analysis:possibly-null-array-index */
    return $table[$key] ?? null;
}

function null_key_on_mixed(mixed $value, null|string $key): mixed
{
    /** @mago-expect analysis:mixed-array-access */
    return $value[$key] ?? null;
}
