<?php

declare(strict_types=1);

function read_in_the_else_branch(mixed $row): mixed
{
    if (!empty($row->phone)) { // @mago-expect analysis:mixed-property-access
        return null;
    }

    return $row->phone; // @mago-expect analysis:mixed-property-access
}

function read_in_an_elseif(mixed $row): string
{
    if (!empty($row->phone) && !empty($row->phone2)) { // @mago-expect analysis:mixed-property-access
        return 'both';
    } elseif (!empty($row->phone)) { // @mago-expect analysis:mixed-property-access
        return 'one';
    }

    return 'none';
}
