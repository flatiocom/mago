<?php

declare(strict_types=1);

final class MixedVariadicSignatureHolder
{
    /** @var callable(mixed...): mixed */
    private $handler;

    public function __construct()
    {
        $this->handler = static fn(mixed ...$arguments): int => count($arguments);
    }

    public function assignNarrower(): void
    {
        // @mago-expect analysis:invalid-property-assignment-value
        $this->handler = static fn(int $value): int => $value;
    }

    public function assignWithoutParameters(): void
    {
        $this->handler = static fn(): int => 1;
    }

    /** @param callable(mixed...): mixed $callback */
    public function take(callable $callback): mixed
    {
        return $callback(1);
    }

    public function passNarrower(): mixed
    {
        // @mago-expect analysis:invalid-argument
        return $this->take(static fn(int $value): int => $value);
    }

    public function passBare(callable $callback): mixed
    {
        return $this->take($callback);
    }

    /**
     * @return callable(mixed...): mixed
     *
     * @mago-expect analysis:invalid-return-statement
     */
    public function give(): callable
    {
        return static fn(int $value): int => $value;
    }

    public function call(): mixed
    {
        return ($this->handler)(1);
    }
}
