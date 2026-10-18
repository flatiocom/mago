<?php

declare(strict_types=1);

function key_of(int|string $key): void
{
    echo $key;
}

function nested_write(mixed $outer, mixed $inner): void
{
    $rows = [];
    // @mago-expect analysis:mixed-array-index,mixed-array-index
    $rows[$outer][$inner] = 1;

    foreach ($rows as $rowKey => $row) {
        key_of($rowKey);
        foreach ($row as $cellKey => $cell) {
            key_of($cellKey);
            echo $cell;
        }
    }
}

function nested_write_on_existing_row(string $outer, mixed $inner): void
{
    $rows = [];
    $rows[$outer] = [];
    // @mago-expect analysis:mixed-array-index
    $rows[$outer][$inner] = 1;

    foreach ($rows[$outer] as $cellKey => $cell) {
        key_of($cellKey);
        echo $cell;
    }
}
