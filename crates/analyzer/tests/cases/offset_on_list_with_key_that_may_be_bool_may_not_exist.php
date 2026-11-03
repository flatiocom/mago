<?php

declare(strict_types=1);

namespace OffsetOnListWithKeyThatMayBeBoolMayNotExist;

/**
 * @param list<int> $value
 */
function int_or_bool_key(array $value, int|bool $key): mixed
{
    // @mago-expect analysis:mismatched-array-index,possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @param list<int> $value
 */
function bool_key(array $value, bool $key): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @param list<int> $value
 */
function true_key(array $value): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-undefined-int-array-index
    return $value[true];
}

/**
 * @param list<int> $value
 */
function int_or_float_key(array $value, int|float $key): mixed
{
    // @mago-expect analysis:mismatched-array-index,possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @param list{int} $value
 */
function bool_key_on_list_shape_without_index_one(array $value, bool $key): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @param list{int, int} $value
 */
function bool_key_on_list_shape_with_both_indexes(array $value, bool $key): mixed
{
    // @mago-expect analysis:invalid-array-index
    return $value[$key];
}

/**
 * @param list{int, int} $value
 */
function true_key_on_list_shape_with_both_indexes(array $value): mixed
{
    // @mago-expect analysis:invalid-array-index
    return $value[true];
}

/**
 * @param non-empty-list<int> $value
 */
function false_key_on_non_empty_list(array $value): mixed
{
    // @mago-expect analysis:invalid-array-index
    return $value[false];
}
