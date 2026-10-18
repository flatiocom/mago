<?php

declare(strict_types=1);

/**
 * @param array<mixed> $values
 */
function offset_write_on_mixed_by_reference(array &$values): void
{
}

function offset_write_on_mixed_compound(mixed $data): void
{
    $data['a'] .= 'x'; // @mago-expect analysis:mixed-array-access,mixed-operand
    $data['b']['c'] += 1; // @mago-expect analysis:mixed-array-access(2),mixed-operand
    $data['d']++; // @mago-expect analysis:mixed-array-access,mixed-operand
}

function offset_write_on_mixed_by_reference_argument(mixed $data): void
{
    offset_write_on_mixed_by_reference($data['v']['w']); // @mago-expect analysis:mixed-array-access(2),mixed-argument
}

function offset_write_on_mixed_narrowed_target(mixed $data): void
{
    if (!$data['a']) { // @mago-expect analysis:mixed-array-access
        $data['a'] = 1;
    }

    if ($data['b']) { // @mago-expect analysis:mixed-array-access
        $data['b']['y'] = 1; // @mago-expect analysis:mixed-array-assignment
    }
}

function offset_write_on_mixed_unset_target(mixed $data): void
{
    if (!isset($data['a'])) { // @mago-expect analysis:mixed-array-access
        $data['a'] = 1; // @mago-expect analysis:mixed-array-assignment
    }

    $data['c'] = 1; // @mago-expect analysis:mixed-array-assignment
}
