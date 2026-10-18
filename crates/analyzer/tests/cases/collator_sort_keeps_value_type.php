<?php

/**
 * @param array<int, mixed> $names
 *
 * @return array<array-key, int>
 */
function index_names(array $names, Collator $collator): array
{
    $collator->asort($names);

    $index = [];
    // @mago-expect analysis:mixed-assignment
    foreach ($names as $name) {
        // @mago-expect analysis:mixed-array-index
        $index[$name] = 1;
    }

    return $index;
}

/**
 * @param array<int, string> $names
 *
 * @return list<string>
 */
function sorted_names(array $names, Collator $collator): array
{
    $collator->sort($names);

    return $names;
}
