<?php

declare(strict_types=1);

namespace LeavingElseTakesOutTheTypesOfOneVariableOnly;

final class Row
{
    public function name(): string
    {
        return 'row';
    }
}

/**
 * @throws \LogicException
 */
function either_null(?Row $first, ?Row $second): string
{
    if ($first === null || $second === null) {
        echo 1;
    } else {
        throw new \LogicException('both');
    }

    return $first !== null ? $first->name() : 'none';
}

/**
 * @throws \LogicException
 */
function elseif_chain(?Row $first, ?Row $second): string
{
    if ($first !== null) {
        $name = $first->name();
    } elseif ($second !== null) {
        $name = $second->name();
    } else {
        throw new \LogicException('none');
    }

    return $name . ($second !== null ? $second->name() : '');
}

/**
 * @throws \LogicException
 *
 * @mago-expect analysis:redundant-comparison
 */
function one_variable(?Row $row): string
{
    if ($row !== null) {
        echo 1;
    } else {
        throw new \LogicException('none');
    }

    return $row !== null ? $row->name() : 'none';
}
