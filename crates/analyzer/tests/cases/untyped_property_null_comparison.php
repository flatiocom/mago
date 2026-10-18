<?php

declare(strict_types=1);

final class UntypedPropertyMinifier
{
    protected $current = '';

    protected ?string $typed = null;

    public function untypedInLoop(): void
    {
        $this->current = "\n";
        while ($this->current !== null) {
            $this->step();
        }
    }

    public function untypedInIf(): void
    {
        $this->current = "\n";
        if ($this->current !== null) {
            $this->step();
        }
    }

    public function untypedIdentical(): bool
    {
        $this->current = 'x';

        return null === $this->current;
    }

    public function typedIsStillReported(): void
    {
        $this->typed = "\n";
        if ($this->typed !== null) { // @mago-expect analysis:redundant-comparison,redundant-condition
            $this->step();
        }
    }

    private function step(): bool
    {
        return true;
    }
}
