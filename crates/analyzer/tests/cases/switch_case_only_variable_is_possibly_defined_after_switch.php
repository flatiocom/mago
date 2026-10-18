<?php

declare(strict_types=1);

/**
 * @param list<string> $keys
 */
function links(array $keys): void
{
    foreach ($keys as $key) {
        switch ($key) {
            case 'order':
                $linkId = 1;
                break;
            default:
                // @mago-expect analysis:possibly-undefined-variable
                echo $linkId;
        }
    }
}
