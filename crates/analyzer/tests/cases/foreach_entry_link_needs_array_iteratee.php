<?php

declare(strict_types=1);

function foreach_entry_link_take(mixed $value): void
{
}

function foreach_entry_link_on_mixed(mixed $values): void
{
    foreach ($values as $key => $value) { // @mago-expect analysis:invalid-iterator,mixed-assignment(2)
        foreach_entry_link_take($values[$key]); // @mago-expect analysis:mixed-array-access
        $values[$key] = $value; // @mago-expect analysis:mixed-array-assignment
    }
}

/**
 * @param array<mixed> $values
 */
function foreach_entry_link_on_array(array $values): void
{
    foreach ($values as $key => $value) { // @mago-expect analysis:mixed-assignment
        foreach_entry_link_take($values[$key]['x']); // @mago-expect analysis:mixed-array-access
        $values[$key]['y'] = $value; // @mago-expect analysis:mixed-array-assignment
    }
}
