<?php

declare(strict_types=1);

final class Ordinal
{
    private const array SUFFIX = ['er' => 'er', 'o' => 'º', 'a' => 'ª'];

    /**
     * @param 'er'|'o'|'a' $suffix
     */
    public static function every_key_defined(string $suffix): string
    {
        return self::SUFFIX[$suffix];
    }

    /**
     * @param 'er'|'o'|'x' $suffix
     */
    public static function one_key_missing(string $suffix): string
    {
        return self::SUFFIX[$suffix]; // @mago-expect analysis:possibly-undefined-string-array-index
    }
}

/**
 * @param array{usd: float, eur: float} $applied
 */
function sealed_shape_every_key_defined(array $applied, string $currency): float
{
    return $applied[$currency === 'USD' ? 'usd' : 'eur'] + 1.0;
}

/**
 * @param array{usd: float, eur?: float} $applied
 */
function sealed_shape_optional_key(array $applied, string $currency): float
{
    return $applied[$currency === 'USD' ? 'usd' : 'eur'] + 1.0; // @mago-expect analysis:possibly-undefined-string-array-index
}

/**
 * @param array{usd: float, eur: float}|array{usd: float} $applied
 */
function union_member_lacks_key(array $applied, string $currency): float
{
    return $applied[$currency === 'USD' ? 'usd' : 'eur'] + 1.0; // @mago-expect analysis:possibly-undefined-string-array-index
}

function constant_array_every_key_defined(int $amount, bool $fx): string
{
    $symbols = ['in_local' => '1', 'in_local_fx' => '2', 'out_local' => '3', 'out_local_fx' => '4'];
    $direction = $amount > 0 ? 'in' : 'out';

    return $symbols[$direction . '_local' . ($fx ? '_fx' : '')];
}

function coalesce_on_every_key_defined(bool $usd): float
{
    $applied = ['usd' => 1.0, 'eur' => 2.0];

    return $applied[$usd ? 'usd' : 'eur'] ?? 0.0; // @mago-expect analysis:redundant-null-coalesce
}
