<?php

declare(strict_types=1);

function takes_int(int $value): void
{
    echo $value;
}

/**
 * @param 1 $value
 */
function takes_one(int $value): void
{
    echo $value;
}

function closures(mixed $raw): void
{
    $declared = function () use ($raw): int {
        // @mago-expect analysis:mixed-return-statement
        return $raw;
    };

    $narrowed = function (): int {
        return 1;
    };

    takes_int($declared());
    takes_one($narrowed());

    $undeclared = fn() => $raw;

    // @mago-expect analysis:mixed-argument
    takes_int($undeclared());
}
