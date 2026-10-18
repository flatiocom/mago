<?php

declare(strict_types=1);

/** @return list<string>|false */
function next_row(): array|false
{
    return time() > 0 ? ['a'] : false;
}

/** @param list<string> $keys */
function rows(array $keys): void
{
    $row = next_row();
    while (($row = next_row()) !== false) {
        /** @mago-expect analysis:shipmonk-variable-type-overwritten */
        $row = array_combine($keys, $row);
        if ($row === []) {
            continue;
        }

        echo count($row);
    }
}

function previous_row_read_in_condition(): void
{
    $row = next_row();
    while (($row = $row === false ? false : next_row()) !== false) {
        if (count($row) === 1) {
            continue;
        }

        echo count($row);
    }
}

function overwritten_by_string(string $label): void
{
    $row = $label;
    /** @mago-expect analysis:shipmonk-variable-type-overwritten */
    while (($row = next_row()) !== false) {
        if (count($row) === 1) {
            continue;
        }

        echo count($row);
    }
}
