<?php

declare(strict_types=1);

namespace NullsafeMagicCallCanReturnNull;

abstract class Module
{
    /**
     * @param list<mixed> $arguments
     */
    public function __call(string $name, array $arguments): bool|int|string
    {
        return 1;
    }
}

final class Modules
{
    public function find(): ?Module
    {
        return null;
    }
}

function title(Modules $modules): bool|int|string
{
    // @mago-expect analysis:non-documented-method
    return $modules->find()?->title() ?? 'none';
}
