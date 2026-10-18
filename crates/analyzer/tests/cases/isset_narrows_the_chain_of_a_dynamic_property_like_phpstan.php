<?php

declare(strict_types=1);

function send(): mixed
{
    return null;
}

function message_link(bool $send): mixed
{
    $response = null;
    if ($send) {
        $response = send(); // @mago-expect analysis:mixed-assignment
    }

    if (isset($response->messages[0]->{'message-id'})) { // @mago-expect analysis:mixed-property-access,mixed-property-access,mixed-array-access
        return $response->messages[0]->{'message-id'}; // @mago-expect analysis:mixed-property-access
    }

    return null;
}
