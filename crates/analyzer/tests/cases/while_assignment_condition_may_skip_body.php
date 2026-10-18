<?php

declare(strict_types=1);

/**
 * @param resource $file
 */
function while_assignment_condition_may_skip_body_lines($file): bool
{
    $lines = [];
    while (($line = fgets($file)) !== false) {
        $lines[] = $line;
    }

    return isset($lines[0]);
}

/**
 * @param resource $file
 */
function while_assignment_condition_may_skip_body_csv($file): bool
{
    $rows = [];
    while (($row = fgetcsv($file, escape: '\\')) !== false) {
        $rows[] = $row;
    }

    return isset($rows[0]);
}
