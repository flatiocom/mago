<?php

declare(strict_types=1);

/**
 * @param array{}|list{string, string, string} $parts
 */
function at_least(array $parts): string
{
    if (count($parts) > 2) {
        return $parts[2];
    }

    return '';
}

/**
 * @param list{string, string, string} $parts
 */
function at_least_on_a_sealed_list(array $parts): string
{
    if (count($parts) >= 3) {
        return $parts[1];
    }

    return '';
}

/**
 * @param array{}|array{a: string, b: string} $parts
 */
function at_least_on_a_keyed_shape(array $parts): string
{
    if (count($parts) > 1) {
        return $parts['a'];
    }

    return '';
}

/**
 * @param array{}|list{string, string} $parts
 */
function truthy(array $parts): string
{
    if ($parts) {
        return $parts[1];
    }

    return '';
}

/**
 * @param list{string, string} $parts
 *
 * @mago-expect analysis:never-return
 */
function at_least_more_than_the_list_holds(array $parts): string
{
    if (count($parts) > 2) {
        return $parts[2];
    }

    return '';
}
