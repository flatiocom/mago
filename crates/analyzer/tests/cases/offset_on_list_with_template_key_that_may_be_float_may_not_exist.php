<?php

declare(strict_types=1);

namespace OffsetOnListWithTemplateKeyThatMayBeFloatMayNotExist;

/**
 * @template K of int|float
 *
 * @param list<int> $value
 * @param K $key
 */
function int_or_float_template_key(array $value, int|float $key): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @template K of float
 *
 * @param list<int> $value
 * @param K $key
 */
function float_template_key(array $value, float $key): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @template K of int|float
 *
 * @param non-empty-list<int> $value
 * @param K $key
 */
function int_or_float_template_key_on_non_empty_list(array $value, int|float $key): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @template K of int|float
 *
 * @param list{int, int} $value
 * @param K $key
 */
function int_or_float_template_key_on_list_shape(array $value, int|float $key): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @template K of int|bool
 *
 * @param list<int> $value
 * @param K $key
 */
function int_or_bool_template_key(array $value, int|bool $key): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-undefined-int-array-index
    return $value[$key];
}
