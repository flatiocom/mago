<?php

declare(strict_types=1);

final class Row
{
    public string $name = '';
}

function takes_string(string $value): void
{
    echo $value;
}

function written_then_isset(mixed $row): void
{
    $row->label = ''; // @mago-expect analysis:mixed-property-access
    if (isset($row->id)) { // @mago-expect analysis:mixed-property-access
        takes_string($row->label);
    }

    takes_string($row->label); // @mago-expect analysis:mixed-property-access
}

function written_then_instanceof(mixed $row): void
{
    $row->label = ''; // @mago-expect analysis:mixed-property-access
    if ($row instanceof Row) {
        takes_string($row->label);
    }
}

function compared_then_empty(mixed $values): mixed
{
    if ($values->type === 0) { // @mago-expect analysis:mixed-property-access
        $values->type = ''; // @mago-expect analysis:mixed-property-access
    }

    if (!empty($values->customer)) { // @mago-expect analysis:mixed-property-access
        return $values->type;
    }

    return null;
}

function only_read_then_isset(mixed $row): mixed
{
    $name = $row->name; // @mago-expect analysis:mixed-property-access,mixed-assignment
    if (isset($row->id)) { // @mago-expect analysis:mixed-property-access
        return $row->name; // @mago-expect analysis:ambiguous-object-property-access
    }

    return $name;
}
