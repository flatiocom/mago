<?php

declare(strict_types=1);

namespace ArrayFilterOfMixedIsMixedArray;

function takesInt(int $value): int
{
    return $value;
}

function firstNumber(mixed $query): int
{
    // @mago-expect analysis:mixed-argument
    $filtered = array_filter($query, static fn (string $key): bool => str_contains($key, '-'), ARRAY_FILTER_USE_KEY);
    // @mago-expect analysis:mixed-assignment
    foreach ($filtered as $value) {
        // @mago-expect analysis:mixed-argument
        return takesInt($value);
    }

    return 0;
}
