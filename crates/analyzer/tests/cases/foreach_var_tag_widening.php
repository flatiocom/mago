<?php

declare(strict_types=1);

final class FeatureMap
{
    public const array MAP = ['a' => 1, 'b' => 2, 'c' => 4];
}

function widened_foreach_value(): int
{
    $sum = 0;
    /** @var int $value */ // @mago-expect analysis:docblock-type-mismatch
    foreach (array_values(FeatureMap::MAP) as $value) {
        $sum += $value;
    }

    return $sum;
}

/**
 * @param list<int|string> $items
 */
function narrowed_foreach_value(array $items): int
{
    $sum = 0;
    /** @var int $item */
    foreach ($items as $item) {
        $sum += $item;
    }

    return $sum;
}
