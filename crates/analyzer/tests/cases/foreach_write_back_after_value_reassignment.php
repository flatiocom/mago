<?php

declare(strict_types=1);

/**
 * @param list<string> $row
 * @return array{name: string}|null
 */
function validate(array $row): ?array
{
    return $row === [] ? null : ['name' => $row[0]];
}

/**
 * @param list<list<string>> $rows
 */
function write_back_after_value_reassignment(array $rows): string
{
    foreach ($rows as $index => $row) {
        $row = array_values($row);
        $rows[$index] = validate($row);
    }

    $names = '';
    foreach ($rows as $row) {
        if ($row !== null) {
            $names .= $row['name'];
        }
    }

    return $names;
}

/**
 * @param list<list<string>> $rows
 */
function write_back_after_key_reassignment(array $rows): string
{
    foreach ($rows as $index => $row) {
        $index = abs($index);
        $rows[$index] = validate($row);
    }

    $names = '';
    foreach ($rows as $row) {
        if ($row !== null) {
            /** @mago-expect analysis:possibly-undefined-int-array-index */
            $names .= $row['name'];
        }
    }

    return $names;
}
