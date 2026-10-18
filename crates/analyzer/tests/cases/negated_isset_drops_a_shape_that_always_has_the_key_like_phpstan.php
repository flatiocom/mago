<?php

declare(strict_types=1);

/**
 * @param array{}|list{string, ''|'a', non-empty-string} $matches
 */
function estimation(array $matches): string
{
    if (!isset($matches[2])) {
        return count($matches) . ' matches';
    }

    return $matches[2];
}

/**
 * @param array{}|list{non-empty-string, ''|'a', non-empty-string} $matches
 */
function merges_the_shapes_after_a_ternary(array $matches): string
{
    $estimation = isset($matches[2]) ? $matches[2] . ' MD' : '';

    return $estimation . $matches[1]; // @mago-expect analysis:possibly-undefined-int-array-index
}
