<?php

declare(strict_types=1);

final class RetryDays
{
    public const array DAYS = [
        'fast' => ['usd' => 2, 'default' => 3],
        'slow' => ['default' => 1],
    ];

    public const array CURRENCIES = ['usd', 'eur'];
}

function retry_days(): int
{
    $sum = 0;
    foreach (RetryDays::CURRENCIES as $currency) {
        $sum += RetryDays::DAYS['slow'][$currency] ?? 0; // @mago-expect analysis:impossible-nonnull-entry-check,redundant-null-coalesce
        $sum += RetryDays::DAYS['fast'][$currency] ?? 0;
    }

    return $sum;
}

/**
 * @param array{default: int}|array{x: int} $days
 * @param 'x'|'y' $key
 */
function key_in_other_variant(array $days, string $key): int
{
    return $days[$key] ?? 0;
}
