<?php

declare(strict_types=1);

/** @param array<int, string|null> $row */
function read_cell(array $row): string
{
    return $row[0] ?? '';
}

/** @param list<string> $row */
function read_strict_cell(array $row): string
{
    return $row[0] ?? '';
}

/** @param resource $handle */
function read_rows($handle): void
{
    while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
        read_cell($row);
    }
}

/** @param resource $handle */
function read_strict_rows($handle): void
{
    $row = fgetcsv($handle, 0, ',', '"', '\\');
    if ($row !== false) {
        /** @mago-expect analysis:possibly-invalid-argument */
        read_strict_cell($row);
    }
}
