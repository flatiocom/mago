<?php

declare(strict_types=1);

/**
 * @property-read bool $hidden
 * @property-read string $title
 */
final class CoalesceMagicRow
{
    public bool $declared = false;

    public function __get(string $name): mixed
    {
        return $name;
    }
}

function coalesce_magic_property(CoalesceMagicRow $row): bool
{
    return $row->hidden ?? false;
}

function coalesce_magic_property_string(CoalesceMagicRow $row): string
{
    return $row->title ?? '';
}

function coalesce_declared_property(CoalesceMagicRow $row): bool
{
    return $row->declared ?? false; // @mago-expect analysis:redundant-null-coalesce
}
