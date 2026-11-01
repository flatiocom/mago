<?php

declare(strict_types=1);

namespace CallableAliasArgument;

function takes_pair(int $first, int $second): void
{
    echo $first + $second;
}

function takes_strings(string $first, string $second): void
{
    echo $first . $second;
}

final class Holder
{
    public function compute(): int
    {
        return 1;
    }

    public function text(): string
    {
        return '';
    }

    /** @param callable(string, string): void $callback */
    public function take(callable $callback): void
    {
        $callback('a', 'b');
    }

    /** @param callable(): string $callback */
    public function takeString(callable $callback): string
    {
        return $callback();
    }

    public function passFunctionName(): void
    {
        // @mago-expect analysis:possibly-invalid-argument
        $this->take('CallableAliasArgument\takes_pair');
    }

    public function passMatchingFunctionName(): void
    {
        $this->take('CallableAliasArgument\takes_strings');
    }

    public function passMethodArray(): string
    {
        // @mago-expect analysis:possibly-invalid-argument
        return $this->takeString([$this, 'compute']);
    }

    public function passMatchingMethodArray(): string
    {
        return $this->takeString([$this, 'text']);
    }
}
