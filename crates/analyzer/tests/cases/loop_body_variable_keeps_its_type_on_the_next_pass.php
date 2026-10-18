<?php

declare(strict_types=1);

function takes_int(int $value): void
{
    echo $value;
}

/**
 * @param list<string> $keys
 */
function links(array $keys): void
{
    foreach ($keys as $key) {
        if ($key === 'text') {
            // @mago-expect analysis:possibly-undefined-variable
            takes_int($linkId);
        }

        if ($key === 'order') {
            $linkId = 1;
        }
    }
}

function counter(): void
{
    $i = 0;
    while ($i < 3) {
        if ($i === 1) {
            // @mago-expect analysis:possibly-undefined-variable
            takes_int($last);
        }

        $last = $i;
        $i++;
    }
}
