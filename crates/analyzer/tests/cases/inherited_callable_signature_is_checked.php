<?php

declare(strict_types=1);

namespace InheritedCallableSignatureIsChecked;

abstract class Extension
{
    /**
     * @return array<string, callable>
     *
     * @mago-expect analysis:missing-callable-signature
     */
    public function getFilters(): array
    {
        return [];
    }
}

final class Filters extends Extension
{
    /**
     * @mago-expect analysis:missing-callable-signature
     */
    public function getFilters(): array
    {
        return ['upper' => strtoupper(...)];
    }
}
