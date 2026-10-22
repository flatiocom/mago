<?php

declare(strict_types=1);

namespace ArrayKeyFirstLastAndCountNarrowing;

/**
 * @param array<string, array<string, float>> $byDay
 *
 * @return array<string, float>
 */
function lastDay(array $byDay): array
{
    if ($byDay === []) {
        return [];
    }

    $last = array_key_last($byDay);

    return $byDay[$last];
}

/**
 * @param array<string, list<string>> $leads
 */
function leads(array $leads, string $key): string
{
    return ($leads[$key] ?? []) === [] ? '' : implode(', ', $leads[$key]);
}

/**
 * @param list<string> $stored
 */
function latest(array $stored, string $day): string
{
    $latest = '';
    $index = 0;
    while ($index < count($stored) && $stored[$index] <= $day) {
        $latest = $stored[$index];
        $index++;
    }

    return $latest;
}

/**
 * @param list<string> $stored
 */
function negativeIndexStaysUnknown(array $stored, int $index): string
{
    if ($index < count($stored)) {
        // @mago-expect analysis:possibly-undefined-int-array-index
        return $stored[$index];
    }

    return '';
}

/**
 * @param list<array{to: ?\DateTimeImmutable}> $intervals
 */
function extend(array $intervals, \DateTimeImmutable $from): \DateTimeImmutable
{
    // @mago-expect analysis:possibly-null-operand
    if ($intervals !== [] && $from <= $intervals[array_key_last($intervals)]['to']) {
        return $intervals[array_key_last($intervals)]['to']->modify('+1 day');
    }

    return $from;
}
