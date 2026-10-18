<?php

declare(strict_types=1);

/**
 * @param array<array-key, mixed> $value
 */
function takes_array(array $value): void
{
    echo count($value);
}

/**
 * @param array<int>|string $value
 */
function literal_key(array|string $value): void
{
    $value['k'] = 1; // @mago-expect analysis:possibly-invalid-array-index
    takes_array($value);
}

/**
 * @param array<int>|string $value
 */
function string_key(array|string $value, string $key): void
{
    $value[$key] = 1; // @mago-expect analysis:possibly-invalid-array-index
    takes_array($value);
}

/**
 * @param array<int>|string $value
 */
function array_key(array|string $value, int|string $key): void
{
    $value[$key] = 1; // @mago-expect analysis:possibly-invalid-array-index
    takes_array($value);
}

/**
 * @param list<int>|string $value
 */
function list_or_string(array|string $value): void
{
    $value['k'] = 1; // @mago-expect analysis:possibly-invalid-array-index
    takes_array($value);
}

/**
 * @param array<string, array<string, int>>|string $value
 */
function nested_write(array|string $value): void
{
    $value['k']['j'] = 1; // @mago-expect analysis:possibly-invalid-array-index
    takes_array($value);
}

/**
 * @param array<string, string>|string $value
 */
function compound_write(array|string $value): void
{
    $value['k'] .= 'x'; // @mago-expect analysis:possibly-invalid-array-index,possibly-undefined-string-array-index
    takes_array($value);
}

function nullable_string(?string $value): void
{
    $value['k'] = 1; // @mago-expect analysis:possibly-invalid-array-index
    takes_array($value);
}

/**
 * @param array<string, mixed>|string $lang
 */
function reported_once_then_narrowed(array|string $lang, int $key): void
{
    $copy = $lang;
    if (is_array($lang)) {
        $copy['filterTable'] = null; // @mago-expect analysis:possibly-invalid-array-index
        $copy['filterTable'][$key] = 1;
        takes_array($copy);
    }
}

/**
 * @param array<int>|string $value
 */
function int_key(array|string $value): string
{
    $value[0] = 'x';

    return is_string($value) ? $value : '';
}

/**
 * @param array<int>|string $value
 */
function append(array|string $value): void
{
    $value[] = 1; // @mago-expect analysis:possibly-invalid-array-index
    takes_array($value);
}

/**
 * @param array<string, list<int>|string> $defaults
 */
function nested_append(array $defaults, string $column): void
{
    $defaults[$column][] = 1; // @mago-expect analysis:possibly-invalid-array-index
    takes_array($defaults[$column]);
}

function append_to_plain_string(string $value): string
{
    $value[] = 'x'; // @mago-expect analysis:invalid-array-index

    return $value;
}

/**
 * @param list<int> $value
 */
function append_to_list(array $value): void
{
    $value[] = 1;
    takes_array($value);
}

/**
 * @param array<string, int>|string $value
 */
function read(array|string $value): mixed
{
    return $value['k'] ?? null;
}

function plain_string(string $value): string
{
    $value['k'] = 'x'; // @mago-expect analysis:invalid-array-index

    return $value;
}
