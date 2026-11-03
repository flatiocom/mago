<?php

declare(strict_types=1);

namespace OffsetOnArrayOrMemberWithoutOffsetsChecksTheArrayKey;

final class Plain
{
    public int $count = 0;
}

/**
 * @template T of array<string, int>
 * @param T|Plain $value
 */
function template_array_or_plain(array|Plain $value, \stdClass $key): mixed
{
    // @mago-expect analysis:invalid-array-access,invalid-array-index
    return $value[$key];
}

/**
 * @template T of array<string, int>|Plain
 * @param T $value
 */
function template_of_array_or_plain(array|Plain $value, \stdClass $key): mixed
{
    // @mago-expect analysis:invalid-array-access,invalid-array-index
    return $value[$key];
}

/**
 * @template T of array<string, int>
 * @param T|Plain $value
 */
function string_key_on_template_array_or_plain(array|Plain $value, string $key): mixed
{
    // @mago-expect analysis:invalid-array-access
    return $value[$key];
}

/**
 * @param array<string, int>|\Closure $value
 */
function object_key_on_array_or_closure(array|\Closure $value, \stdClass $key): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-invalid-array-access
    return $value[$key];
}

/**
 * @param array<string, int>|object $value
 */
function object_key_on_array_or_object(array|object $value, \stdClass $key): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-invalid-array-access
    return $value[$key];
}

/**
 * @param array<string, int>|int $value
 */
function object_key_on_array_or_int(array|int $value, \stdClass $key): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-invalid-array-access
    return $value[$key];
}

/**
 * @param array<string, int>|int $value
 */
function string_key_on_array_or_int(array|int $value, string $key): mixed
{
    // @mago-expect analysis:possibly-invalid-array-access
    return $value[$key];
}

function object_key_on_closure_alone(\Closure $value, \stdClass $key): mixed
{
    // @mago-expect analysis:invalid-array-access
    return $value[$key];
}
