<?php

declare(strict_types=1);

/**
 * @param non-negative-int $exponent
 */
function literal_base_non_negative_exponent(int $exponent): int
{
    // @mago-expect analysis:redundant-cast
    return (int) (2 ** $exponent);
}

/**
 * @param int<0, 5> $exponent
 *
 * @return int<1, 32>
 */
function literal_base_bounded_exponent(int $exponent): int
{
    return 2 ** $exponent;
}

function literal_base_any_exponent(int $exponent): int
{
    return (int) (2 ** $exponent);
}

function non_literal_base(int $base): int
{
    return (int) ($base ** 2);
}

function exponent_one(int $base): int
{
    // @mago-expect analysis:redundant-cast
    return (int) ($base ** 1);
}

function negative_literal_exponent(): float
{
    return 2 ** -1;
}

function overflowing_literal_power(): float
{
    return 2 ** 64;
}

/**
 * @param list<int> $exponents
 *
 * @return list<int>
 */
function exponent_in_loop(int $base, array $exponents): array
{
    $powers = [];
    foreach ($exponents as $exponent) {
        $powers[] = (int) ($base ** $exponent);
    }

    return $powers;
}
