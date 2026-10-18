<?php

declare(strict_types=1);

/**
 * @param array<string, int> $data
 */
function two_reads(array $data, string $key): int
{
    $first = $data[$key] ?? 0;
    $second = $data[$key]; // @mago-expect analysis:possibly-undefined-string-array-index
    $third = $data[$key]; // @mago-expect analysis:possibly-undefined-string-array-index

    return $first + $second + $third;
}

/**
 * @param non-empty-list<array{to: int, ends: bool}> $merged
 */
function list_reads(array $merged): int
{
    $total = 0;
    for ($i = 0, $count = count($merged) - 1; $i < $count; $i++) {
        $total += $merged[$i]['to']; // @mago-expect analysis:possibly-undefined-int-array-index
        if ($total > 3) {
            $total += $merged[$i]['ends'] ? 2 : 1; // @mago-expect analysis:possibly-undefined-int-array-index
        }
    }

    return $total;
}

/**
 * @param array<string, int|null> $map
 */
function narrowed_read_stays_silent(array $map, string $key): int
{
    if ($map[$key] !== null) { // @mago-expect analysis:possibly-undefined-string-array-index
        return $map[$key];
    }

    return 0;
}

/**
 * @param array<string, int> $map
 */
function isset_read_stays_silent(array $map, string $key): int
{
    if (isset($map[$key])) {
        return $map[$key] + $map[$key];
    }

    return 0;
}
