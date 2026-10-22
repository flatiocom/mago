<?php

declare(strict_types=1);

namespace UnresolvableIndexAccessParameterIsNotChecked;

final class Writer
{
    /**
     * @param int[string] $path
     */
    public function write($path = null): string
    {
        return '';
    }
}

function call(Writer $writer, string $path): string
{
    return $writer->write($path);
}
