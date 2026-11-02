<?php

declare(strict_types=1);

namespace ReadonlyPropertyTypeNotKeptWhenACloneReassignsIt;

class Base
{
}

final class Child extends Base
{
}

function take_child(Child $child): void
{
}

final class ResetInClone
{
    /**
     * @throws \LogicException
     */
    public function __construct(
        private readonly Base $base,
        private readonly Base $kept,
    ) {
        if (!$this->base instanceof Child || !$this->kept instanceof Child) {
            throw new \LogicException();
        }
    }

    public function __clone()
    {
        $this->base = new Base();
    }

    public function read(): void
    {
        // @mago-expect analysis:less-specific-argument
        take_child($this->base);
        take_child($this->kept);
    }
}

final class ResetWithClone
{
    /**
     * @throws \LogicException
     */
    public function __construct(
        private readonly Base $base,
        private readonly Base $kept,
    ) {
        if (!$this->base instanceof Child || !$this->kept instanceof Child) {
            throw new \LogicException();
        }
    }

    public function reset(): self
    {
        return clone($this, ['base' => new Base()]);
    }

    public function read(): void
    {
        // @mago-expect analysis:less-specific-argument
        take_child($this->base);
        take_child($this->kept);
    }
}

final class ResetWithUnknownProperties
{
    /**
     * @throws \LogicException
     */
    public function __construct(
        private readonly Base $base,
    ) {
        if (!$this->base instanceof Child) {
            throw new \LogicException();
        }
    }

    /**
     * @param array<string, Base> $properties
     */
    public function reset(array $properties): self
    {
        return clone($this, $properties);
    }

    public function read(): void
    {
        // @mago-expect analysis:less-specific-argument
        take_child($this->base);
    }
}
