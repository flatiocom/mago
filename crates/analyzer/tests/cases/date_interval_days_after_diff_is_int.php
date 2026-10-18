<?php

declare(strict_types=1);

function takes_int(int $days): int
{
    return $days;
}

function days_after_diff(DateTimeInterface $a, DateTime $b, DateTimeImmutable $c): int
{
    return takes_int($a->diff($b)->days)
        + takes_int($b->diff($c)->days)
        + takes_int($c->diff($a)->days)
        + takes_int(date_diff($a, $b)->days);
}

function days_decremented_after_diff(DateTime $a, DateTime $b): int
{
    $interval = $a->diff($b);
    $days = $interval->days;
    $days--;

    return $days;
}

/**
 * @param DateInterval&object{days: int} $interval
 */
function days_of_shaped_interval(DateInterval $interval): int
{
    return $interval->days;
}

function days_of_constructed_interval(): int
{
    // @mago-expect analysis:possibly-false-argument
    return takes_int((new DateInterval('P1D'))->days);
}

function days_of_declared_interval(DateInterval $interval): int
{
    // @mago-expect analysis:possibly-false-argument
    return takes_int($interval->days);
}
