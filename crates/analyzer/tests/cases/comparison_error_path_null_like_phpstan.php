<?php

declare(strict_types=1);

final class Visit
{
    public DateTimeImmutable $time;
    public null|DateTimeImmutable $end = null;

    public function __construct()
    {
        $this->time = new DateTimeImmutable();
    }
}

function compare_after_null_receiver(null|Visit $visit, DateTimeImmutable $at): bool
{
    /** @mago-expect analysis:possibly-null-property-access */
    return $visit->time <= $at;
}

function compare_nullable_property(null|Visit $visit, DateTimeImmutable $at): bool
{
    /** @mago-expect analysis:possibly-null-property-access,possibly-null-operand */
    return $visit->end <= $at;
}

function spaceship_after_null_receiver(null|Visit $visit, DateTimeImmutable $at): int
{
    /** @mago-expect analysis:possibly-null-property-access */
    return $visit->time <=> $at;
}

function spaceship_nullable_property(Visit $visit, DateTimeImmutable $at): int
{
    /** @mago-expect analysis:possibly-null-operand */
    return $visit->end <=> $at;
}
