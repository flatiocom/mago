<?php

declare(strict_types=1);

namespace Fixture;

/**
 * @property callable $onResult
 */
class Form
{
    /** @var array<callable> */
    public array $onCreateForm = [];

    public function nativeClosure(callable $c, \Closure $d): \Closure
    {
        return $d;
    }
}
