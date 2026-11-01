<?php

declare(strict_types=1);

function ternary_keeps_the_exclusion(mixed $value): bool
{
    $data = $value !== [] ? $value : null; // @mago-expect analysis:mixed-assignment
    if ($data === [] || $data === null) { // @mago-expect analysis:redundant-comparison,redundant-type-comparison
        return false;
    }

    return true;
}

function empty_array_member_drops_the_exclusion(mixed $value): bool
{
    $data = $value !== [] ? $value : []; // @mago-expect analysis:mixed-assignment
    if ($data === [] || $data === null) {
        return false;
    }

    return true;
}

function plain_mixed(mixed $value): bool
{
    if ($value === [] || $value === null) {
        return false;
    }

    return true;
}
