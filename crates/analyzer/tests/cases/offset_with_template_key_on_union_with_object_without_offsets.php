<?php

declare(strict_types=1);

namespace OffsetWithTemplateKeyOnUnionWithObjectWithoutOffsets;

final class Plain
{
    public int $count = 0;
}

/**
 * @template K of string
 * @param list<int>|Plain $value
 * @param K $key
 */
function string_template_key_on_list_or_plain(array|Plain $value, string $key): mixed
{
    // @mago-expect analysis:invalid-array-access
    return $value[$key];
}

/**
 * @template K of string
 * @param array<int, int>|Plain $value
 * @param K $key
 */
function string_template_key_on_int_keys_or_plain(array|Plain $value, string $key): mixed
{
    // @mago-expect analysis:invalid-array-access
    return $value[$key];
}

/**
 * @template K of int
 * @param array<string, int>|Plain $value
 * @param K $key
 */
function int_template_key_on_string_keys_or_plain(array|Plain $value, int $key): mixed
{
    // @mago-expect analysis:invalid-array-access
    return $value[$key];
}
