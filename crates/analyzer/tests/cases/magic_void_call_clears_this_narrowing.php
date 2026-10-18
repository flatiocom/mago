<?php

declare(strict_types=1);

final class Row
{
    public int $id = 1;
}

final class Form
{
    private ?Row $row = null;

    public function __construct(
        private readonly ?Row $fixed,
    ) {}

    /**
     * @param list<mixed> $arguments
     */
    public function __call(string $name, array $arguments): void
    {
    }

    public function save(): void
    {
        if ($this->row === null) {
            $this->row = new Row();
        }

        $this->onResult(true); // @mago-expect analysis:non-documented-method
        echo $this->row->id; // @mago-expect analysis:possibly-null-property-access
    }

    public function readonly_stays_narrowed(): void
    {
        if ($this->fixed === null) {
            return;
        }

        $this->onResult(true); // @mago-expect analysis:non-documented-method
        echo $this->fixed->id;
    }

    public function readonly_stays_narrowed_after_own_method(): void
    {
        if ($this->fixed === null) {
            return;
        }

        $this->helper();
        echo $this->fixed->id;
    }

    private function helper(): void
    {
    }
}
