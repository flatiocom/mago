<?php

declare(strict_types=1);

/** @param array<int, array{open: list<string>}|null> $skip */
function isset_after_null_entry_check(array $skip, int $index): int
{
    if (array_key_exists($index, $skip) && $skip[$index] === null) {
        return 0;
    }

    if (isset($skip[$index])) {
        return count($skip[$index]['open']);
    }

    return 1;
}

/** @param array<int, array{open: list<string>}|null> $skip */
function isset_after_absent_key(array $skip, int $index): int
{
    if (array_key_exists($index, $skip)) {
        return 0;
    }

    if (isset($skip[$index])) {
        return count($skip[$index]['open']);
    }

    return 1;
}

/**
 * @param array<int, array{open: list<string>}|null> $skip
 * @param list<string> $pairs
 */
function isset_with_a_narrower_key(array $skip, array $pairs): int
{
    $count = 0;
    foreach ($pairs as $index => $_pair) {
        if (array_key_exists($index, $skip) && $skip[$index] === null) {
            continue;
        }

        if (isset($skip[$index])) {
            $count += count($skip[$index]['open']);
        }
    }

    return $count;
}
