<?php

declare(strict_types=1);

/**
 * @return list<string>
 */
function reset_after_isset(string $key): array
{
    $values = explode('|', $key);
    if (isset($values[3])) {
        echo $values[3];
        $values = [];
    }

    return $values;
}

/**
 * @param array{a: int, ...<string, string>} $shape
 * @return array<string, int|string>
 */
function reset_unsealed_shape(array $shape): array
{
    echo $shape['a'];
    /** @mago-expect analysis:shipmonk-variable-type-overwritten */
    $shape = [];

    return $shape;
}
