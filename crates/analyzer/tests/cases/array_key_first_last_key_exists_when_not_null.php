<?php

declare(strict_types=1);

/**
 * @param list<array{0: string, 1: string}> $periods
 *
 * @return list<array{from: string, to: string}>
 */
function merged(array $periods): array
{
    $merged = [];
    foreach ($periods as $period) {
        $last = array_key_last($merged);

        if ($last !== null && $period[0] <= $merged[$last]['to']) {
            $merged[$last]['to'] = max($merged[$last]['to'], $period[1]);
            continue;
        }

        $merged[] = ['from' => $period[0], 'to' => $period[1]];
    }

    return $merged;
}

/** @param array<string, int> $counts */
function first_count(array $counts): int
{
    $first = array_key_first($counts);
    if ($first === null) {
        return 0;
    }

    return $counts[$first];
}

/** @param list<int> $values */
function unchecked(array $values): int
{
    $last = array_key_last($values);

    /** @mago-expect analysis:possibly-null-array-index,possibly-undefined-int-array-index */
    return $values[$last];
}

/**
 * @param list<int> $values
 * @param list<int> $others
 */
function other_array(array $values, array $others): int
{
    $last = array_key_last($values);
    if ($last === null) {
        return 0;
    }

    /** @mago-expect analysis:possibly-undefined-int-array-index */
    return $others[$last];
}

/** @param list<int> $values */
function reassigned_key(array $values, int $index): int
{
    $last = array_key_last($values);
    $last = $last === null ? null : $index;
    if ($last === null) {
        return 0;
    }

    /** @mago-expect analysis:possibly-undefined-int-array-index */
    return $values[$last];
}
