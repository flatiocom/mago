<?php

declare(strict_types=1);

namespace Fixture;

use Closure;

interface FormFactory
{
    /** @mago-expect analysis:missing-callable-signature */
    public function create(callable $onSuccess): object;
}

// @mago-expect analysis:missing-callable-signature
/**
 * @property callable $onResult
 * @property callable(string): void $onError
 */
class Form
{
    /**
     * @var array<callable>
     *
     * @mago-expect analysis:missing-callable-signature
     */
    public array $onCreateForm = [];

    /**
     * @var array<callable|Closure>
     *
     * @mago-expect analysis:missing-callable-signature(2)
     */
    public array $onValidate = [];

    /** @var list<callable(int): void> */
    public array $onSubmit = [];

    /** @mago-expect analysis:missing-callable-signature */
    public Closure $factory;

    /** @mago-expect analysis:missing-callable-signature */
    public function nativeCallable(callable $c): void
    {
    }

    /** @mago-expect analysis:missing-callable-signature */
    public function nullableCallable(?callable $c = null): void
    {
    }

    /** @mago-expect analysis:missing-callable-signature(2) */
    public function nativeClosure(Closure $c): Closure
    {
        return $c;
    }

    /**
     * @return array<string, callable>
     *
     * @mago-expect analysis:missing-callable-signature
     */
    public function handlers(): array
    {
        return [];
    }

    /**
     * @param Closure(callable): void $c
     *
     * @mago-expect analysis:missing-callable-signature
     */
    public function nestedInClosure(Closure $c): void
    {
    }

    /**
     * @return callable(): callable
     *
     * @mago-expect analysis:missing-callable-signature
     */
    public function nestedInReturn(): callable
    {
        return static fn(): callable => strlen(...);
    }

    /**
     * @param callable(int): void $c
     * @param Closure(): void $d
     * @param callable-string $e
     */
    public function withSignatures(callable $c, Closure $d, string $e): void
    {
    }
}

class ChildForm extends Form
{
}

/** @mago-expect analysis:missing-callable-signature(2) */
function compose(callable $first, Closure $second): void
{
}

function untouchedClosures(): void
{
    $closure = static function (callable $c): callable {
        return $c;
    };

    $arrow = static fn(Closure $c): Closure => $c;
}
