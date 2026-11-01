<?php

declare(strict_types=1);

function filled(?string $error): bool
{
    return $error && $error !== '0'; // @mago-expect analysis:redundant-comparison
}

function filled_mixed(mixed $error): bool
{
    return $error && $error !== '0'; // @mago-expect analysis:mixed-operand,redundant-comparison
}

function missing_entry(mixed $entry): bool
{
    return empty($entry) || $entry === false; // @mago-expect analysis:redundant-comparison
}

/**
 * @param non-empty-list<string> $row
 */
function empty_row(array $row): bool
{
    return $row === []; // @mago-expect analysis:redundant-comparison
}
