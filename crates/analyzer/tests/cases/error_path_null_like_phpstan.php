<?php

declare(strict_types=1);

final class ErrorPathRow
{
    public int $id = 1;

    public ?string $name = null;

    public function label(): string
    {
        return 'row';
    }
}

function error_path_literal_name_on_nullable(?ErrorPathRow $row): int
{
    return $row->id; // @mago-expect analysis:possibly-null-property-access
}

function error_path_error_path_null_in_concat(?ErrorPathRow $row): string
{
    return 'id ' . $row->id; // @mago-expect analysis:possibly-null-property-access
}

function error_path_error_path_null_in_arithmetic(?ErrorPathRow $row): int
{
    return $row->id + 1; // @mago-expect analysis:possibly-null-property-access
}

function error_path_takes_int(int $value): int
{
    return $value;
}

function error_path_error_path_null_as_argument(?ErrorPathRow $row): int
{
    return error_path_takes_int($row->id); // @mago-expect analysis:possibly-null-property-access
}

function error_path_error_path_null_in_chain(?ErrorPathRow $row): string
{
    return $row->label() . $row->name; // @mago-expect analysis:possible-method-access-on-null,possibly-null-property-access,possibly-null-operand
}

/**
 * @param array{list?: list<int>, row?: ErrorPathRow, nested?: array{id: int}} $data
 * @param list<string> $names
 */
function error_path_missing_key_reads(array $data, array $names): string
{
    $id = $data['nested']['id']; // @mago-expect analysis:possibly-undefined-string-array-index
    $name = $data['row']->name ?? '';
    $label = $data['row']->label(); // @mago-expect analysis:possibly-undefined-string-array-index
    $first = $names[$data['nested']['id']]; // @mago-expect analysis:possibly-undefined-string-array-index
    $sum = 0;
    foreach ($data['list'] as $value) { // @mago-expect analysis:possibly-undefined-string-array-index
        $sum += $value;
    }

    return $id . $name . $label . $first . $sum;
}
