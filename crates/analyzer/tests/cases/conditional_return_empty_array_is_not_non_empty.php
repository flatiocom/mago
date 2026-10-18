<?php

declare(strict_types=1);

function last_of_empty(): null
{
    return array_key_last([]);
}

function first_of_empty(): null
{
    $items = [];

    return array_key_first($items);
}

/** @param list<int> $items */
function last_of_list(array $items): null
{
    /** @mago-expect analysis:nullable-return-statement,invalid-return-statement */
    return array_key_last($items);
}

/** @param non-empty-list<int> $items */
function last_of_non_empty(array $items): int
{
    return array_key_last($items);
}
