<?php

declare(strict_types=1);

namespace UniversalObjectCrateBehindErrorPathNull;

final class Holder
{
    public \stdClass $row;

    public function __construct()
    {
        $this->row = new \stdClass();
    }
}

function read(?Holder $holder, string $name): mixed
{
    // @mago-expect analysis:possibly-null-property-access
    return $holder->row->{$name};
}
