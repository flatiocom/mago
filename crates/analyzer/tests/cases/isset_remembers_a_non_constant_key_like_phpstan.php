<?php

declare(strict_types=1);

/** @param array<string, string> $titles */
function known_language(array $titles, mixed $row): mixed
{
    if (isset($titles[$row['language']])) { // @mago-expect analysis:mixed-array-access,mixed-array-index
        return $row['language'];
    }

    return null;
}
