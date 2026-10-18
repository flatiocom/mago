<?php

declare(strict_types=1);

/**
 * @param array{a: int, b?: string} $arr
 */
function unguarded(array $arr): string
{
    // @mago-expect analysis:possibly-undefined-string-array-index
    return $arr['b'];
}
