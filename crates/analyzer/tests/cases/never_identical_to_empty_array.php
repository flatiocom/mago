<?php

declare(strict_types=1);

namespace NeverIdenticalToEmptyArray;

/**
 * @param non-empty-array<string, int> $values
 */
function non_empty_array(array $values): bool
{
    return $values === []; // @mago-expect analysis:redundant-comparison
}

/**
 * @param non-empty-list<int> $values
 */
function non_empty_list_not_identical(array $values): bool
{
    return $values !== []; // @mago-expect analysis:redundant-comparison
}

function truthy_mixed(mixed $value): bool
{
    if ($value) {
        return $value === []; // @mago-expect analysis:redundant-comparison
    }

    return false;
}

function mixed_compared_not_identical_first(mixed $value): bool
{
    if ($value !== []) {
        return $value === []; // @mago-expect analysis:redundant-comparison
    }

    return false;
}

/**
 * @param array<string, int> $values
 */
function possibly_empty_array(array $values): bool
{
    return $values === [];
}

/**
 * @param array{x?: int} $values
 */
function optional_shape(array $values): bool
{
    return $values === [];
}

function falsy_mixed(mixed $value): bool
{
    if (!$value) {
        return $value === [];
    }

    return false;
}
