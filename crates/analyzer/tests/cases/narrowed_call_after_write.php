<?php

declare(strict_types=1);

namespace NarrowedCallAfterWrite;

/**
 * @param array<string, string> $values
 */
function unset_between(array $values): int
{
    if (intval($values['k'] ?? '')) {
        unset($values['j']);
        if (is_int(intval($values['k'] ?? ''))) { // @mago-expect analysis:redundant-type-comparison
            return 1;
        }
    }

    return 0;
}

/**
 * @param array<string, string> $values
 */
function offset_written_between(array $values): int
{
    if (intval($values['k'] ?? '')) {
        $values['j'] = '1';
        if (is_int(intval($values['k'] ?? ''))) { // @mago-expect analysis:redundant-type-comparison
            return 1;
        }
    }

    return 0;
}

function variable_written_between(string $value): int
{
    if (intval($value)) {
        $value = 'x';
        if (is_int(intval($value))) { // @mago-expect analysis:redundant-type-comparison
            return 1;
        }
    }

    return 0;
}

/**
 * @param array<string, string> $values
 */
function nothing_written_between(array $values): int
{
    if (intval($values['k'] ?? '')) {
        if (is_int(intval($values['k'] ?? ''))) {
            return 1;
        }
    }

    return 0;
}
