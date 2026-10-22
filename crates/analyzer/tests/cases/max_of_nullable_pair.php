<?php

declare(strict_types=1);

namespace MaxOfNullablePair;

function laterOfDates(?\DateTimeImmutable $first, \DateTimeImmutable $second): string
{
    return max($first, $second)->format('Y');
}

function earlierOfDates(?\DateTimeImmutable $first, \DateTimeImmutable $second): string
{
    // @mago-expect analysis:possible-method-access-on-null
    return min($first, $second)->format('Y');
}

function bothNullable(?\DateTimeImmutable $first, ?\DateTimeImmutable $second): string
{
    // @mago-expect analysis:possible-method-access-on-null
    return max($first, $second)->format('Y');
}

/**
 * @mago-expect analysis:nullable-return-statement,invalid-return-statement
 */
function integersKeepNull(?int $first, int $second): int
{
    return max($first, $second);
}
