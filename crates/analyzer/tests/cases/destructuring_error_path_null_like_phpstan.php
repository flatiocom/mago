<?php

declare(strict_types=1);

/**
 * @param list<array{string, non-empty-string}> $matches
 */
function destructure_possibly_missing_match(array $matches, int $index): string
{
    /** @mago-expect analysis:possibly-undefined-int-array-index */
    [, $name] = $matches[$index];

    return '{' . $name . '}';
}

/**
 * @param null|array{string, non-empty-string} $match
 */
function destructure_nullable_match(null|array $match): string
{
    /** @mago-expect analysis:possibly-null-destructuring-source */
    [, $name] = $match;

    /** @mago-expect analysis:possibly-null-operand */
    return '{' . $name . '}';
}
