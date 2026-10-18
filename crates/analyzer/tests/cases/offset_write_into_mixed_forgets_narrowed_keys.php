<?php

declare(strict_types=1);

function offset_write_into_mixed_take(mixed $value): void
{
}

function offset_write_into_mixed_forgets_sibling(mixed $data): void
{
    if (is_array($data['a'])) { // @mago-expect analysis:mixed-array-access
        $data['p'] = 1; // @mago-expect analysis:mixed-array-assignment
        offset_write_into_mixed_take($data['a']['x']); // @mago-expect analysis:mixed-array-access(2)
    }
}

function offset_write_into_mixed_keeps_written_path(mixed $data): void
{
    if ($data['a']) { // @mago-expect analysis:mixed-array-access
        $data['a']['y'] = 1; // @mago-expect analysis:mixed-array-assignment
        offset_write_into_mixed_take($data['a']['x']); // @mago-expect analysis:mixed-array-access
    }
}

/**
 * @param array<string, mixed> $data
 */
function offset_write_into_array_keeps_sibling(array $data): void
{
    if (isset($data['a']) && is_array($data['a']) && isset($data['a']['x'])) {
        $data['p'] = 1;
        offset_write_into_mixed_take($data['a']['x']);
    }
}
