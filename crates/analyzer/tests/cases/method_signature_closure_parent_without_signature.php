<?php

declare(strict_types=1);

class ClosureParentWithoutSignature
{
    public function run(Closure $callback): void
    {
        $callback();
    }

    /** @param Closure(int): mixed $callback */
    public function narrow(Closure $callback): void
    {
        $callback(1);
    }
}

class ClosureChildWithSignature extends ClosureParentWithoutSignature
{
    /**
     * @param Closure(int): mixed $callback
     *
     * @mago-expect analysis:incompatible-parameter-type
     */
    public function run(Closure $callback): void
    {
        $callback(1);
    }

    public function narrow(Closure $callback): void
    {
        $callback(1);
    }
}
