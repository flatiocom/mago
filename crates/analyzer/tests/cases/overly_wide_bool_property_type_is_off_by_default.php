<?php

declare(strict_types=1);

final class Flags
{
    private bool $neverTrue = false;

    public function read(): bool
    {
        return $this->neverTrue;
    }
}
