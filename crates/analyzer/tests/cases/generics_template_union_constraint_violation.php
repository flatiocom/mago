<?php

declare(strict_types=1);

/**
 * @template T of int|string
 */
final class GenIntOrStr2
{
    /** @param T $value */
    public function __construct(public int|string $value)
    {
    }
}

/** @mago-expect analysis:template-constraint-violation,invalid-argument */
/** @mago-expect analysis:unused-statement */
new GenIntOrStr2(true);
