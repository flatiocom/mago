<?php

declare(strict_types=1);

namespace ArrayIndexNamingUnknownClass;

function group(mixed $raw): void
{
    $groups = [];
    // @mago-expect analysis:non-existent-class-like
    /** @var \string $key */
    $key = $raw;
    $groups[$key][] = 1;

    var_dump($groups);
}
