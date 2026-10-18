<?php

declare(strict_types=1);

function clone_nullable_date(null|DateTime $date): bool
{
    /** @mago-expect analysis:possibly-invalid-clone */
    $copy = clone $date;

    return $copy <= new DateTime();
}

function compare_nullable_date(null|DateTime $date): bool
{
    /** @mago-expect analysis:possibly-null-operand */
    return $date <= new DateTime();
}
