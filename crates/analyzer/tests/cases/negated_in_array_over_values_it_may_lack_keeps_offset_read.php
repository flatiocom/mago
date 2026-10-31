<?php

declare(strict_types=1);

namespace NegatedInArrayOverValuesItMayLackKeepsOffsetRead;

/**
 * @param list<'a'|'b'> $seen
 *
 * @mago-expect analysis:mixed-array-access(2)
 */
function typed_list(mixed $row, array $seen): int
{
    if (in_array($row['url'], $seen, true)) {
        return 0;
    }

    echo serialize($row['url']);

    return count($seen);
}

/**
 * @mago-expect analysis:mixed-array-access
 */
function literal_list(mixed $row): int
{
    if (in_array($row['url'], ['a', 'b'], true)) {
        return 0;
    }

    echo serialize($row['url']);

    return 1;
}
