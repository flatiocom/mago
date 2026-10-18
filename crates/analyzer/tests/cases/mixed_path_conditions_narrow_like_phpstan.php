<?php

declare(strict_types=1);

function mixed_path_conditions_take(mixed $value): void
{
}

function mixed_path_if_narrows_the_checked_offset(mixed $data): void
{
    if ($data['a']) { // @mago-expect analysis:mixed-array-access
        mixed_path_conditions_take($data['a']['t']); // @mago-expect analysis:mixed-array-access
        mixed_path_conditions_take($data['b']); // @mago-expect analysis:mixed-array-access
    }
}

function mixed_path_elseif_narrows_the_checked_offset(mixed $data): void
{
    if ($data['p']) { // @mago-expect analysis:mixed-array-access
        mixed_path_conditions_take($data['p']);
    } elseif ($data['a']) { // @mago-expect analysis:mixed-array-access
        mixed_path_conditions_take($data['a']['t']); // @mago-expect analysis:mixed-array-access
    }
}

function mixed_path_ternary_narrows_the_checked_offset(mixed $data): mixed
{
    return $data['a'] ? $data['a']['t'] : null; // @mago-expect analysis:mixed-array-access(2)
}

function mixed_path_merge_keeps_the_checked_offset(mixed $data): void
{
    if ($data['a']) { // @mago-expect analysis:mixed-array-access
        mixed_path_conditions_take(1);
    }

    mixed_path_conditions_take($data['a']);
}
