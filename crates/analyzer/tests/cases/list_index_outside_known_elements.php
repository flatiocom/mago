<?php

declare(strict_types=1);

/** @param non-empty-list<string|null> $line */
function read_after_empty_check(array $line): ?string
{
    $title = empty($line[4]) ? '' : 'set';
    echo $title;

    /** @mago-expect analysis:possibly-undefined-int-array-index */
    return $line[5];
}

/** @param non-empty-list<string|null> $line */
function read_checked_index(array $line): ?string
{
    if (!isset($line[4])) {
        return null;
    }

    return $line[4];
}

/** @param non-empty-list<string|null> $line */
function read_with_default(array $line): ?string
{
    $title = empty($line[4]) ? '' : 'set';
    echo $title;

    return $line[5] ?? null;
}

/** @param non-empty-list<string|null> $line */
function check_other_index(array $line): bool
{
    $title = empty($line[4]) ? '' : 'set';
    echo $title;

    return isset($line[5]);
}

function read_past_a_minimum_count(string $key): string
{
    $parts = explode(':', $key);
    if (count($parts) >= 3) {
        /** @mago-expect analysis:possibly-undefined-int-array-index */
        return $parts[2] . $parts[3];
    }

    return '';
}

function minimum_count_covers_ten_elements(string $key): string
{
    $parts = explode(':', $key);
    if (count($parts) >= 12) {
        /** @mago-expect analysis:possibly-undefined-int-array-index */
        return $parts[9] . $parts[10];
    }

    return '';
}

function read_around_a_set_index(string $key): string
{
    $values = explode('|', $key);
    if (isset($values[3])) {
        /** @mago-expect analysis:possibly-undefined-int-array-index */
        return $values[0] . $values[2] . $values[3] . $values[4];
    }

    return '';
}
