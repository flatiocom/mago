<?php

declare(strict_types=1);

namespace OffsetOnListWithNumericMixedOrBoundedTemplateKeyMayNotExist;

/**
 * @template K of numeric
 *
 * @param list<int> $value
 * @param K $key
 */
function numeric_template_key(array $value, $key): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @template K of numeric
 *
 * @param list{int, int} $value
 * @param K $key
 */
function numeric_template_key_on_list_shape(array $value, $key): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @template K of array-key
 *
 * @param list<int> $value
 * @param K $key
 */
function array_key_template_key(array $value, int|string $key): mixed
{
    // @mago-expect analysis:possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @template K of int|string
 *
 * @param non-empty-list<int> $value
 * @param K $key
 */
function int_or_string_template_key_on_non_empty_list(array $value, int|string $key): mixed
{
    // @mago-expect analysis:possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @param list<int> $value
 */
function array_key_key(array $value, int|string $key): mixed
{
    // @mago-expect analysis:possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @param list<int> $value
 */
function mixed_key(array $value, mixed $key): mixed
{
    // @mago-expect analysis:mixed-array-index,possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @param list{int, int} $value
 */
function mixed_key_on_list_shape(array $value, mixed $key): mixed
{
    // @mago-expect analysis:mixed-array-index,possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @param list<int> $value
 * @param numeric $key
 */
function numeric_key(array $value, $key): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @param non-empty-list<int> $value
 * @param numeric $key
 */
function numeric_key_on_non_empty_list(array $value, $key): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @template J of float
 * @template K of J
 *
 * @param list<int> $value
 * @param K $key
 */
function template_key_bounded_by_float_template(array $value, float $key): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-undefined-int-array-index
    return $value[$key];
}

/**
 * @template J of float
 * @template K of J|int
 *
 * @param list<int> $value
 * @param K $key
 */
function template_key_bounded_by_float_template_or_int(array $value, int|float $key): mixed
{
    // @mago-expect analysis:invalid-array-index,possibly-undefined-int-array-index
    return $value[$key];
}
