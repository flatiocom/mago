<?php

declare(strict_types=1);

namespace OffsetOnArrayOrObjectWithoutArrayAccessChecksTheArrayKey;

final class Plain
{
    public int $count = 0;
}

/**
 * @param array<string, int>|Plain $value
 */
function object_key_read(array|Plain $value, \stdClass $key): mixed
{
    // @mago-expect analysis:invalid-array-access,invalid-array-index
    return $value[$key];
}

/**
 * @param array<string, int>|Plain $value
 */
function array_key_read(array|Plain $value): mixed
{
    // @mago-expect analysis:invalid-array-access,invalid-array-index
    return $value[[]];
}

/**
 * @param array<string, int>|Plain $value
 */
function object_key_write(array|Plain $value, \stdClass $key): void
{
    // @mago-expect analysis:invalid-array-access,invalid-array-index
    $value[$key] = 1;
}

/**
 * @param list<int>|Plain $value
 */
function string_key_on_list(array|Plain $value): mixed
{
    // @mago-expect analysis:invalid-array-access
    return $value['x'];
}

/**
 * @param array<'a'|'b', int>|Plain $value
 */
function int_key_on_string_keys(array|Plain $value, int $key): mixed
{
    // @mago-expect analysis:invalid-array-access
    return $value[$key];
}

/**
 * @param array{}|Plain $value
 * @param array-key $key
 */
function array_key_on_empty_array(array|Plain $value, int|string $key): mixed
{
    // @mago-expect analysis:invalid-array-access
    return $value[$key];
}

function object_key_on_plain_alone(Plain $value, \stdClass $key): mixed
{
    // @mago-expect analysis:invalid-array-access
    return $value[$key];
}

function object_key_on_string_or_plain(string|Plain $value, \stdClass $key): mixed
{
    // @mago-expect analysis:invalid-array-access
    return $value[$key];
}
