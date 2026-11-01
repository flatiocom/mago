<?php

declare(strict_types=1);

namespace NativeTypesOfBuiltInCalls;

/**
 * @param resource $handle
 *
 * @return list<string|null>
 *
 * @mago-expect analysis:impossible-type-comparison
 * @mago-expect analysis:redundant-comparison
 */
function first_row($handle): array
{
    $row = fgetcsv($handle, 0, ',', '"', '');
    if (!is_array($row) || $row === []) {
        return [];
    }

    return $row;
}

/**
 * @param array{date: string} $data
 *
 * @throws \InvalidArgumentException
 *
 * @mago-expect analysis:redundant-cast
 */
function date_of(array $data): string
{
    if (preg_match('/^\d{4}$/', $data['date']) !== 1) {
        throw new \InvalidArgumentException('Invalid date.');
    }

    return (string) $data['date'];
}

/**
 * @param array{date: string} $data
 */
function unchecked_date_of(array $data): string
{
    return (string) $data['date'];
}

function first_part(string $key): string
{
    $parts = explode(':', $key, 2);

    return $parts[0] ?? '';
}
