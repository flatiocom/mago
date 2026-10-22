<?php

declare(strict_types=1);

namespace MissingShapeKeyReadNarrowsLikeMixed;

/**
 * @param array{limit?: int} $attributes
 */
function defaultValue(array $attributes): \DateTimeImmutable|int|null
{
    // @mago-expect analysis:undefined-string-array-index
    $value = $attributes['default'];
    if (is_string($value)) {
        return new \DateTimeImmutable($value);
    }

    return null;
}

/**
 * @param array{limit?: int} $attributes
 */
function returned(array $attributes): string
{
    // @mago-expect analysis:undefined-string-array-index,invalid-return-statement
    return $attributes['default'];
}
