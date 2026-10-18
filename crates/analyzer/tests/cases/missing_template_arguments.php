<?php

declare(strict_types=1);

namespace Fixture;

/** @template T */
class Holder
{
    /** @mago-expect analysis:missing-template-parameter */
    public function self(): self
    {
        return $this;
    }

    public function stat(): static
    {
        return $this;
    }

    /** @return Holder<T> */
    public function typed(): Holder
    {
        return $this;
    }
}

// @mago-expect analysis:unused-template-parameter
/** @template T = int */
class Defaulted
{
}

// @mago-expect analysis:unused-template-parameter(2)
/**
 * @template TKey
 * @template TValue
 */
interface Pairs
{
}

/**
 * @template T of Holder
 */
class Bounded
{
    /** @param T $holder */
    public function bound(Holder $holder): void
    {
    }
}

// @mago-expect analysis:missing-template-parameter
/**
 * @property Holder $magic
 * @property Holder<int> $typedMagic
 */
class User
{
    /** @mago-expect analysis:missing-template-parameter */
    public Holder $native;

    /**
     * @var Holder
     *
     * @mago-expect analysis:missing-template-parameter
     */
    public object $docblock;

    /**
     * @var list<Holder|Pairs>
     *
     * @mago-expect analysis:missing-template-parameter(2)
     */
    public array $nested = [];

    /** @var Holder<string> */
    public Holder $typed;

    /** @mago-expect analysis:missing-template-parameter */
    public function __construct(
        public Holder $promoted,
    ) {
        $this->native = $promoted;
        $this->docblock = $promoted;
        $this->typed = new Holder();
    }

    /** @mago-expect analysis:missing-template-parameter(2) */
    public function nullable(?Holder $holder): ?Holder
    {
        return $holder;
    }

    /** @mago-expect analysis:missing-template-parameter */
    public function union(Holder|int $holder): void
    {
    }

    /** @mago-expect analysis:missing-template-parameter(3) */
    public function iterables(\Traversable $traversable, \Iterator $iterator, \ArrayAccess $access, \Generator $generator): void
    {
    }

    /**
     * @param \Iterator<int> $iterator
     * @param \Generator<int, string> $generator
     *
     * @mago-expect analysis:missing-template-parameter
     *
     * @return \Generator
     */
    public function documentedIterables(\Iterator $iterator, \Generator $generator): \Generator
    {
        yield from $generator;
        yield $iterator->current();
    }

    public function defaulted(Defaulted $defaulted): void
    {
    }

    /** @param Holder<int> $holder */
    public function documented(Holder $holder): void
    {
    }

    public function closure(): void
    {
        $closure = function (Holder $holder): Holder {
            return $holder;
        };
        $closure($this->native);
    }
}

/** @mago-expect analysis:missing-template-parameter(2) */
function take(Holder $holder): Holder
{
    return $holder;
}
