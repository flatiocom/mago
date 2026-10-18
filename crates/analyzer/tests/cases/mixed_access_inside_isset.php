<?php

declare(strict_types=1);

function offset_on_left_of_coalesce(mixed $value): mixed
{
    return $value['key'] ?? null; // @mago-expect analysis:mixed-array-access
}

function offset_in_isset(mixed $value): bool
{
    return isset($value['key']); // @mago-expect analysis:mixed-array-access
}

function offset_in_empty(mixed $value): bool
{
    return empty($value['key']); // @mago-expect analysis:mixed-array-access
}

/**
 * @param array<string, mixed> $values
 */
function nested_offset_on_left_of_coalesce(array $values): mixed
{
    return $values['outer']['inner'] ?? null; // @mago-expect analysis:mixed-array-access
}

function property_on_left_of_coalesce(mixed $value): mixed
{
    return $value->name ?? null; // @mago-expect analysis:mixed-property-access
}

function property_in_isset(mixed $value): bool
{
    return isset($value->name); // @mago-expect analysis:mixed-property-access
}

/**
 * @param array<string, int>|null $values
 */
function nullable_array_stays_silent(array|null $values): int
{
    return $values['key'] ?? 0;
}

function nullable_object_stays_silent(stdClass|null $value): mixed
{
    return $value->name ?? null;
}
