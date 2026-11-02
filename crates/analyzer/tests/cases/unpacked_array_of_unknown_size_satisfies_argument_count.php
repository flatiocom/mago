<?php

declare(strict_types=1);

namespace UnpackedArrayOfUnknownSizeSatisfiesArgumentCount;

final class Filter
{
    public function __construct(
        public int $limit,
        public string $name,
    ) {}
}

function join_two(int $limit, string $name): string
{
    return $limit . $name;
}

/**
 * @param array<string, mixed> $values
 */
function from_map(array $values): Filter
{
    // @mago-expect analysis:mixed-argument
    return new Filter(...$values);
}

/**
 * @param list<int|string> $values
 */
function from_list(array $values): string
{
    // @mago-expect analysis:possibly-invalid-argument
    return join_two(...$values);
}

/**
 * @param array{limit: int} $values
 */
function from_short_shape(array $values): Filter
{
    // @mago-expect analysis:too-few-arguments
    return new Filter(...$values);
}

/**
 * @param array{limit: int, name: string} $values
 */
function from_full_shape(array $values): Filter
{
    return new Filter(...$values);
}

function from_empty_list(): string
{
    // @mago-expect analysis:too-few-arguments
    return join_two(...[]);
}
