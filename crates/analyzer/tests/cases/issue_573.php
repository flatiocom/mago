<?php

declare(strict_types=1);

class Foo
{
    /** @var array<string, string> */
    private const ORDER_BY = [
        'id' => 'a.id',
    ];

    /** @var array<string, string> */
    private static array $DIRECTION = [
        'asc' => 'ASC',
        'desc' => 'DESC',
    ];

    private function _bar(null|string $order, null|string $direction): void
    {
        // @mago-expect analysis:possibly-null-array-index,possibly-null-array-index
        if (isset(self::ORDER_BY[$order], self::$DIRECTION[$direction])) {
            // @mago-expect analysis:possibly-null-array-index
            $order = self::ORDER_BY[$order];
            // @mago-expect analysis:possibly-null-array-index
            $direction = self::$DIRECTION[$direction];
        }
    }
}
