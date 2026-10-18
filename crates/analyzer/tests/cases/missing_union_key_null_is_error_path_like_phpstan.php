<?php

declare(strict_types=1);

/**
 * @param array<string, array{original: float, exchanged: float}> $sum
 *
 * @return array<string, array{original: float, exchanged?: float}>
 */
function add_to_both_sums(array $sum, string $currency, float $amount): array
{
    /** @mago-expect analysis:possibly-undefined-string-array-index */
    $sum[$currency]['original'] += $amount;
    /** @mago-expect analysis:possibly-undefined-string-array-index */
    $sum[$currency]['exchanged'] += $amount;

    return $sum;
}

/**
 * @param array{a: int}|array{a: int, b: null|int} $row
 */
function add_to_nullable_variant_key(array $row): int
{
    /** @mago-expect analysis:possibly-undefined-string-array-index,possibly-null-operand */
    return $row['b'] + 1;
}
