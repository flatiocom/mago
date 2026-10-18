<?php

declare(strict_types=1);

final class Repro
{
    // @mago-expect analysis:unused-property-type
    private ?string $a = null;
    // @mago-expect analysis:unused-property-type
    private ?string $b = null;

    public function noop(): void {}

    public function takes(string $v): void {}

    public function trigger(): void
    {
        if (null === $this->a || null === $this->b) {
            $this->noop();
        }

        /** @mago-expect analysis:possibly-null-argument */
        $this->takes($this->a);

        /** @mago-expect analysis:possibly-null-argument */
        $this->takes($this->b);
    }
}
