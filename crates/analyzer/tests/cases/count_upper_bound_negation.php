<?php

declare(strict_types=1);

/**
 * @param list{string, 1?: string, 2?: string} $parts
 */
function fewer_than_three_returns(array $parts): string
{
    if (count($parts) < 3) {
        return '';
    }

    return $parts[2];
}

/**
 * @param list{string, 1?: string, 2?: string} $parts
 */
function at_most_two_returns(array $parts): string
{
    if (count($parts) <= 2) {
        return '';
    }

    return $parts[2];
}

/**
 * @param list{string, 1?: string, 2?: string} $parts
 */
function three_greater_returns(array $parts): string
{
    if (3 > count($parts)) {
        return '';
    }

    return $parts[2];
}

/**
 * @param list{string, 1?: string, 2?: string} $parts
 */
function fewer_than_two_returns(array $parts): string
{
    if (count($parts) < 2) {
        return '';
    }

    /** @mago-expect analysis:possibly-undefined-int-array-index */
    return $parts[1] . $parts[2];
}
