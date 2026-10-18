<?php

declare(strict_types=1);

/**
 * @param list<int|string> $value
 */
function take_list(array $value): void
{
}

/**
 * @param non-empty-list<string> $value
 */
function take_non_empty_list(array $value): void
{
}

/**
 * @param array<string, int> $value
 */
function take_string_keyed(array $value): void
{
}

function take_string(string $value): void
{
}

/**
 * @param list<int> $list
 * @param array<int, string> $intKeyed
 * @param non-empty-array<int, string> $nonEmptyIntKeyed
 * @param array<string, int> $stringKeyed
 */
function merge(array $list, array $intKeyed, array $nonEmptyIntKeyed, array $stringKeyed): void
{
    take_list(array_merge($list, $intKeyed));
    take_list(array_merge($intKeyed));
    take_non_empty_list(array_merge($nonEmptyIntKeyed, $intKeyed));

    take_string_keyed(array_merge($list, $stringKeyed)); // @mago-expect analysis:possibly-invalid-argument

    $merged = array_merge($list, ['x']);
    take_string($merged[0]); // @mago-expect analysis:possibly-invalid-argument
}
