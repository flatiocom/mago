<?php

declare(strict_types=1);

function probe(?string $subject): string
{
    /** @mago-expect analysis:possibly-null-argument */
    return str_replace('_', '-', $subject);
}

function probe_insensitive(?string $subject): string
{
    /** @mago-expect analysis:possibly-null-argument */
    return str_ireplace('_', '-', $subject);
}

/** @param string|list<string> $subject */
function probe_union(string|array $subject): string
{
    /** @mago-expect analysis:invalid-return-statement */
    return str_replace('_', '-', $subject);
}
