<?php

declare(strict_types=1);

namespace OffsetWithKeyThatMayBeBoolIsInvalid;

final class Plain
{
    public int $count = 0;
}

/**
 * @param array<string, int> $value
 */
function string_or_bool_key_on_array(array $value, string|bool $key): mixed
{
    // @mago-expect analysis:mismatched-array-index
    return $value[$key];
}

/**
 * @param array<string, int>|Plain $value
 */
function string_or_bool_key_on_array_or_plain(array|Plain $value, string|bool $key): mixed
{
    // @mago-expect analysis:invalid-array-access,invalid-array-index
    return $value[$key];
}

/**
 * @param list<int> $value
 */
function int_or_bool_key_on_list(array $value, int|bool $key): mixed
{
    // @mago-expect analysis:mismatched-array-index
    return $value[$key];
}

/**
 * @param array{a: int, b: int} $value
 */
function string_or_bool_key_on_shape(array $value, string|bool $key): mixed
{
    // @mago-expect analysis:mismatched-array-index
    return $value[$key];
}

/**
 * @param array<string, int> $value
 */
function string_key_on_array(array $value, string $key): mixed
{
    return $value[$key];
}

/**
 * @param array<string, int> $value
 */
function string_or_bool_key_write(array $value, string|bool $key): array
{
    // @mago-expect analysis:mismatched-array-index
    $value[$key] = 1;

    return $value;
}
