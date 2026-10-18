<?php

declare(strict_types=1);

function note(): void
{
    echo 'missing';
}

function read_after_a_non_leaving_branch(mixed $files, string $id): mixed
{
    if (!isset($files[$id])) { // @mago-expect analysis:mixed-array-access
        note();
    }

    return $files[$id]; // @mago-expect analysis:mixed-array-access
}

function read_after_a_leaving_branch(mixed $files, string $id): mixed
{
    if (!isset($files[$id])) { // @mago-expect analysis:mixed-array-access
        return null;
    }

    return $files[$id];
}
