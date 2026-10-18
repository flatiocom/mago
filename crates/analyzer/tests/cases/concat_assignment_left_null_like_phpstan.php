<?php

declare(strict_types=1);

function append_to_nullable(?string &$log, string $line): void
{
    $log .= $line;
}

function append_to_null(string $line): string
{
    $log = null;
    $log .= $line;

    return $log;
}

function append_nullable_right(string $log, ?string $line): string
{
    /** @mago-expect analysis:possibly-null-operand */
    $log .= $line;

    return $log;
}

function append_nullable_inside_right(?string $log, ?string $first, string $second): string
{
    /** @mago-expect analysis:possibly-null-operand */
    $log .= $first . $second;
    /** @mago-expect analysis:possibly-null-operand */
    $log .= strtoupper($first . $second);

    return $log;
}

function add_to_nullable(?int $count): int
{
    /** @mago-expect analysis:possibly-null-operand */
    $count += 1;

    return $count;
}
