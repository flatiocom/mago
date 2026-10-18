<?php

/**
 * @param array<string, int> $map
 */
function read_nullable_key_twice(array $map, ?string $key): int
{
    // @mago-expect analysis:possibly-null-array-index
    if (isset($map[$key])) {
        // @mago-expect analysis:possibly-null-array-index
        return $map[$key];
    }

    return 0;
}

/**
 * @param array<array-key, int> $map
 * @return array<array-key, int>
 */
function unset_after_isset(array $map, string|false $key): array
{
    // @mago-expect analysis:mismatched-array-index
    if (isset($map[$key])) {
        // @mago-expect analysis:mismatched-array-index
        unset($map[$key]);
    }

    return $map;
}

final class Row
{
    public string $name = '';
}

/**
 * @param array<string, Row> $rows
 */
function name_after_truthy_check(array $rows, mixed $lang): string
{
    // @mago-expect analysis:mixed-array-index
    // @mago-expect analysis:mixed-array-index
    if (isset($rows[$lang]) && $rows[$lang]->name) {
        // @mago-expect analysis:mixed-array-index
        return $rows[$lang]->name;
    }

    return '';
}

/**
 * @param array<string, int> $map
 */
function read_string_key_twice(array $map, string $key): int
{
    if (isset($map[$key])) {
        return $map[$key];
    }

    return 0;
}

/**
 * @param array<array-key, string> $parameters
 */
function parameter_after_key_exists(array $parameters, mixed $name): ?string
{
    // @mago-expect analysis:mixed-argument
    if (array_key_exists($name, $parameters)) {
        return $parameters[$name];
    }

    return null;
}

function banner_image(int $pageId): string
{
    $images = [
        1 => 'page1.jpg',
        42 => 'page42.jpg',
        'default' => 'default.jpg',
    ];

    return isset($images[$pageId]) ? $images[$pageId] : $images['default'];
}
