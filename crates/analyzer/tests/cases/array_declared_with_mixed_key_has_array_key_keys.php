<?php

declare(strict_types=1);

function key_of(int|string $key): void
{
    echo $key;
}

/**
 * @param array<mixed, int> $pairs
 */
function array_pairs(array $pairs): void
{
    foreach ($pairs as $key => $value) {
        key_of($key);
        echo $value;
    }

    foreach (array_keys($pairs) as $key) {
        key_of($key);
    }
}

/**
 * @param iterable<mixed, int> $pairs
 */
function iterable_pairs(iterable $pairs): void
{
    // @mago-expect analysis:mixed-assignment
    foreach ($pairs as $key => $value) {
        // @mago-expect analysis:mixed-argument
        key_of($key);
        echo $value;
    }
}
