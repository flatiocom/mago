<?php

declare(strict_types=1);

function takes_date(null|DateTimeImmutable $date): void
{
    echo $date?->format('Y');
}

function switch_without_default(stdClass $values, int $kind): void
{
    switch ($kind) {
        case 1:
            $values->to = new DateTimeImmutable();
            break;
        case 2:
            break;
    }

    takes_date($values->to); // @mago-expect analysis:mixed-argument
}

function switch_with_default(stdClass $values, int $kind): void
{
    switch ($kind) {
        case 1:
            $values->to = new DateTimeImmutable();
            break;
        default:
            break;
    }

    takes_date($values->to); // @mago-expect analysis:mixed-argument
}

function every_case_sets_it(stdClass $values, int $kind): void
{
    switch ($kind) {
        case 1:
            $values->to = new DateTimeImmutable();
            break;
        default:
            $values->to = null;
            break;
    }

    takes_date($values->to);
}
