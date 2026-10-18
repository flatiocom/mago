<?php

declare(strict_types=1);

final class TagRecord
{
    public const string ItemId = 'item_id';
    public const int First = 0;

    /**
     * @param array{type: string, item_id?: int} $data
     */
    public static function itemId(array $data): int
    {
        if (isset($data[self::ItemId])) {
            return $data[self::ItemId];
        }

        return 0;
    }
}

/**
 * @param array{type: string, item_id?: int} $data
 */
function constant_key_isset(array $data): int
{
    if (isset($data[TagRecord::ItemId])) {
        return $data['item_id'];
    }

    return 0;
}

/**
 * @param array{0?: string} $names
 */
function constant_index_isset(array $names): string
{
    if (isset($names[TagRecord::First])) {
        return $names[TagRecord::First];
    }

    return '';
}
