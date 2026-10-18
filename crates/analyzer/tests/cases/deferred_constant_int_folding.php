<?php

declare(strict_types=1);

final class BitwiseItems
{
    public const int First = 1;
    public const int Second = 2;
}

final class Toppings
{
    public const int Cheese = BitwiseItems::First;
    public const int Olives = BitwiseItems::Second;
    public const int Combined = self::Cheese | self::Olives;
    public const int Chained = self::Combined << 2 | self::Shifted;
    public const int Shifted = BitwiseItems::Second * 32;
    public const array MAP = ['cheese' => self::Cheese, 'combined' => self::Combined];
}

const TOPPINGS_ALL = Toppings::Combined | 8;

/**
 * @return 3
 */
function combined_toppings(): int
{
    return Toppings::Combined;
}

/**
 * @return 76
 */
function chained_toppings(): int
{
    return Toppings::Chained;
}

/**
 * @return 11
 */
function global_toppings(): int
{
    return TOPPINGS_ALL;
}

function widened_toppings_value(): int
{
    $sum = 0;
    /** @var int $value */ // @mago-expect analysis:docblock-type-mismatch
    foreach (array_values(Toppings::MAP) as $value) {
        $sum += $value;
    }

    return $sum;
}
