<?php

declare(strict_types=1);

/**
 * @param non-empty-string|'' $value
 */
function string_covers_empty_or_non_empty(string $value): string
{
    /** @var string $copy */
    $copy = $value;

    return $copy;
}

/**
 * @param lowercase-string&non-empty-string|'' $value
 */
function lowercase_string_covers_empty_or_lowercase_non_empty(string $value): string
{
    /** @var lowercase-string $copy */
    $copy = $value;

    return $copy;
}

/**
 * @param non-falsy-string|'' $value
 */
function string_widens_empty_or_non_falsy(string $value): string
{
    /** @mago-expect analysis:docblock-type-mismatch */
    /** @var string $copy */
    $copy = $value;

    return $copy;
}
