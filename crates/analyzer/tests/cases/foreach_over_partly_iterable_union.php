<?php

declare(strict_types=1);

namespace ForeachOverPartlyIterableUnion;

/**
 * @param array<string, int>|int|null $values
 */
function keys($values): int
{
    $sum = 0;
    // @mago-expect analysis:possibly-null-iterator,possibly-invalid-iterator
    foreach ($values as $key => $value) {
        $sum += strlen($key) + $value;
    }

    return $sum;
}
