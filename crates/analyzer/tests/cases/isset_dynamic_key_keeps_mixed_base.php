<?php

declare(strict_types=1);

function isset_dynamic_key_take(mixed $value): void
{
}

function isset_dynamic_key_keeps_narrowed_offset(mixed $pair, string $key): void
{
    if (!$pair['row']) { // @mago-expect analysis:mixed-array-access
        return;
    }

    if (!isset($pair[$key])) { // @mago-expect analysis:mixed-array-access
        return;
    }

    isset_dynamic_key_take($pair['row']['id']); // @mago-expect analysis:mixed-array-access
    isset_dynamic_key_take($pair['other']); // @mago-expect analysis:mixed-array-access
    isset_dynamic_key_take($pair[$key]);
}

function isset_dynamic_key_branch_keeps_narrowed_offset(mixed $pair, string $key): void
{
    if (!$pair['row']) { // @mago-expect analysis:mixed-array-access
        return;
    }

    if (isset($pair[$key])) { // @mago-expect analysis:mixed-array-access
        isset_dynamic_key_take($pair['row']['id']); // @mago-expect analysis:mixed-array-access
    }
}

function isset_dynamic_key_forgets_narrowed_property(mixed $row, string $key): void
{
    if (!$row->name) { // @mago-expect analysis:mixed-property-access
        return;
    }

    if (isset($row[$key])) { // @mago-expect analysis:mixed-array-access
        isset_dynamic_key_take($row->name); // @mago-expect analysis:mixed-property-access
    }
}
