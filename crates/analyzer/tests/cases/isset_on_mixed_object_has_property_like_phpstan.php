<?php

declare(strict_types=1);

function nested_isset(mixed $row): void
{
    if (isset($row->customer->phone)) { // @mago-expect analysis:mixed-property-access,mixed-property-access
        echo $row->customer->other; // @mago-expect analysis:ambiguous-object-property-access,mixed-argument
        echo $row->other; // @mago-expect analysis:ambiguous-object-property-access,mixed-argument
    }
}

function second_empty_reads_a_known_object(mixed $row): void
{
    if (!empty($row->phone) && !empty($row->phone2)) { // @mago-expect analysis:mixed-property-access
        echo 'both';
    }
}

function dynamic_name_is_not_a_property_assertion(mixed $photo, string $name): void
{
    if (!isset($photo->$name)) { // @mago-expect analysis:string-member-selector
        return;
    }

    echo $photo->id; // @mago-expect analysis:mixed-property-access,mixed-argument
}
