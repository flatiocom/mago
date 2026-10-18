<?php

declare(strict_types=1);

namespace Fixture;

/** @template T */
class Holder
{
    public function self(): self
    {
        return $this;
    }

    /** @return Holder<T> */
    public function typed(): Holder
    {
        return $this;
    }
}

function pass(Holder $holder): Holder
{
    return $holder;
}
