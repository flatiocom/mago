<?php

declare(strict_types=1);

namespace ForeachReassignedKeyForgetsOuterReads;

/**
 * @param array<string, string> $data
 */
function read(array $data): void
{
    foreach (['country', 'place'] as $type) {
        // @mago-expect analysis:possibly-undefined-string-array-index
        if ($data[$type] === '') {
            continue;
        }

        echo $data[$type];
    }

    foreach (['city', 'district'] as $type) {
        // @mago-expect analysis:possibly-undefined-string-array-index
        if ($data[$type] === '') {
            continue;
        }

        foreach (explode(',', $data[$type]) as $part) {
            echo $part;
        }
    }
}
