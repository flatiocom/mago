<?php

declare(strict_types=1);

/**
 * @param array<array-key, mixed> $itemData
 */
function unsealed_shape_read(array $itemData): mixed
{
    $itemData['price_tiers'] = [];

    return $itemData['price']; // @mago-expect analysis:possibly-undefined-string-array-index
}

/**
 * @param array<array-key, mixed> $itemData
 */
function unsealed_shape_known_key(array $itemData): mixed
{
    $itemData['price_tiers'] = [];

    return $itemData['price_tiers'];
}

/**
 * @param array<array-key, mixed> $itemData
 */
function unsealed_shape_isset(array $itemData): mixed
{
    $itemData['price_tiers'] = [];

    return $itemData['price'] ?? null;
}
