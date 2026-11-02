<?php

declare(strict_types=1);

namespace UnpackedIterableChecksFirstParameter;

function join_two(int $limit, string $name): string
{
    return $limit . $name;
}

/**
 * @param iterable<string> $values
 */
function from_iterable_of_strings(iterable $values): string
{
    // @mago-expect analysis:invalid-argument
    return join_two(...$values);
}

/**
 * @param iterable<int> $values
 */
function from_iterable_of_ints(iterable $values): string
{
    return join_two(...$values);
}

/**
 * @param \Generator<int, string> $values
 */
function from_generator_of_strings(\Generator $values): string
{
    // @mago-expect analysis:invalid-argument
    return join_two(...$values);
}

/**
 * @param iterable<string, mixed> $values
 */
function from_iterable_of_mixed(iterable $values): string
{
    // @mago-expect analysis:mixed-argument
    return join_two(...$values);
}
