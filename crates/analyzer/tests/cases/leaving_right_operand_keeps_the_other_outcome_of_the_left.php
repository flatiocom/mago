<?php

declare(strict_types=1);

namespace LeavingRightOperandKeepsTheOtherOutcomeOfTheLeft;

final class Row
{
    public function name(): string
    {
        return 'row';
    }
}

function fail(): never
{
    exit(1);
}

function note(): bool
{
    return true;
}

function and_throw(?Row $row): string
{
    $row === null && throw new \LogicException('none');

    return $row->name();
}

function or_throw(?Row $row): string
{
    $row !== null || throw new \LogicException('none');

    return $row->name();
}

function and_never_call(?Row $row): string
{
    !$row && fail();

    return $row->name();
}

/**
 * @mago-expect analysis:possible-method-access-on-null
 */
function and_returning_call(?Row $row): string
{
    $row === null && note();

    return $row->name();
}

/**
 * @mago-expect analysis:possible-method-access-on-null
 */
function nested_and_throw(?Row $row, bool $flag): string
{
    $flag && $row === null && throw new \LogicException('none');

    return $row->name();
}
