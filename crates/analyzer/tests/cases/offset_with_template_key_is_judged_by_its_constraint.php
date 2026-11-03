<?php

declare(strict_types=1);

namespace OffsetWithTemplateKeyIsJudgedByItsConstraint;

final class Plain
{
    public int $count = 0;
}

/**
 * @template K of array-key
 * @param array<string, int>|Plain $value
 * @param K $key
 */
function template_key_on_array_or_plain(array|Plain $value, int|string $key): mixed
{
    // @mago-expect analysis:invalid-array-access
    return $value[$key];
}

/**
 * @template K of array-key
 * @param array<string, int> $value
 * @param K $key
 */
function template_key_on_array(array $value, int|string $key): mixed
{
    return $value[$key];
}

/**
 * @template K of string
 * @param array<string, int> $value
 * @param K $key
 */
function string_template_key_on_array(array $value, string $key): mixed
{
    return $value[$key];
}

/**
 * @template K of \stdClass
 * @param array<string, int>|Plain $value
 * @param K $key
 */
function object_template_key_on_array_or_plain(array|Plain $value, \stdClass $key): mixed
{
    // @mago-expect analysis:invalid-array-access,invalid-array-index
    return $value[$key];
}
