<?php

declare(strict_types=1);

/** @return list<array{to: string}> */
function never_written(): array
{
    $rows = [];
    $key = null;

    /** @mago-expect analysis:redundant-comparison,impossible-condition */
    if ($key !== null) {
        $rows[$key]['to'] = 'x';
    }

    return $rows;
}

/**
 * @param array<string, int> $rows
 *
 * @return list<int>
 */
function written(array $rows, string $key): array
{
    $rows[$key] = 1;

    /** @mago-expect analysis:invalid-return-statement */
    return $rows;
}
