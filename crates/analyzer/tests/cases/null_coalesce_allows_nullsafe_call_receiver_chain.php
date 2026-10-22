<?php

declare(strict_types=1);

namespace NullCoalesceAllowsNullsafeCallReceiverChain;

final class Title
{
    public function name(): ?string
    {
        return null;
    }

    public function format(int $width): ?string
    {
        return (string) $width;
    }
}

final class Holder
{
    public ?Title $title = null;
    public Title $fixed;

    public function __construct()
    {
        $this->fixed = new Title();
    }
}

function throughNullsafeCall(?Holder $holder): string
{
    return $holder->title?->name() ?? 'none';
}

function plainCallStillChecksItsReceiver(?Holder $holder): string
{
    // @mago-expect analysis:possibly-null-property-access
    return $holder->fixed->name() ?? 'none';
}

function argumentsAreNotInTheChain(?Holder $holder, ?int $width): string
{
    // @mago-expect analysis:possibly-null-argument
    return $holder->title?->format($width) ?? 'none';
}
