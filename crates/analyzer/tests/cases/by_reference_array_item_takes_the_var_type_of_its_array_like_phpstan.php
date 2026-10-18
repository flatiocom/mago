<?php

declare(strict_types=1);

/** @param iterable<string, mixed> $data */
function update(iterable $data): void
{
    echo count([...$data]);
}

/** @param array<string, int> $data */
function takes_counts(array $data): void
{
    echo count($data);
}

/** @param int<0, 9> $digit */
function takes_digit(int $digit): void
{
    echo $digit;
}

/** @param array{} $data */
function takes_empty(array $data): void
{
    echo count($data);
}

/** @param 5 $five */
function takes_five(int $five): void
{
    echo $five;
}

function columns_with_a_var_tag(): void
{
    $userData = [];
    $groupData = [];
    $count = 5;

    /** @var list<array{col: string, data: array<string, mixed>, count: int<0, 9>}> $columns */
    $columns = [
        ['col' => 'a', 'data' => &$userData, 'count' => &$count],
        ['col' => 'b', 'data' => &$groupData, 'count' => 1],
    ];
    echo count($columns);

    takes_digit($count);
    $userData['updated'] = 1;
    update($userData);
    update($groupData);
}

function nested_columns_with_a_var_tag(): void
{
    $data = [];

    /** @var array{x: array{data: array<string, int>}} $columns */
    $columns = ['x' => ['data' => &$data]];
    echo count($columns);

    takes_counts($data);
}

function columns_without_a_var_tag(): void
{
    $data = [];
    $five = 5;
    $columns = ['x' => ['data' => &$data], 'y' => &$five];
    echo count($columns);

    takes_empty($data);
    takes_five($five);
}
