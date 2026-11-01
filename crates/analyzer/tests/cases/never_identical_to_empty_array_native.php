<?php

declare(strict_types=1);

namespace NeverIdenticalToEmptyArrayNative;

/**
 * @param non-empty-list<int> $values
 */
function documented_non_empty_list(array $values): bool
{
    return $values === [];
}

/**
 * @param resource $handle
 */
function csv_row($handle): bool
{
    $row = fgetcsv($handle);

    return $row !== false && $row === []; // @mago-expect analysis:redundant-comparison,redundant-logical-operation
}
