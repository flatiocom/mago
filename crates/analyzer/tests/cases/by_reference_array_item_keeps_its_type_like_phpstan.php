<?php

declare(strict_types=1);

function takes_int(int $value): void
{
    echo $value;
}

/** @param array<string, string> $row */
function collect(array $row): int
{
    $count = 0;
    $data = [];
    $columns = [['data' => &$data, 'count' => &$count]];
    echo count($columns);

    $data['route'] = $row['street'] ?? '';

    takes_int($count);

    return count($data);
}
