<?php

declare(strict_types=1);

/** @implements ArrayAccess<array-key, string> */
final class Bag implements ArrayAccess
{
    public function offsetExists(mixed $offset): bool
    {
        return true;
    }

    public function offsetGet(mixed $offset): string
    {
        return '';
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    public function offsetUnset(mixed $offset): void
    {
    }
}

final class Holder
{
    public function __construct(
        private Bag $bag,
    ) {
    }

    public function fromProperty(string $key): string
    {
        if (!isset($this->bag[$key])) {
            return '';
        }

        return $this->bag[$key];
    }

    public function fromLocal(Bag $bag, int $key): Bag
    {
        if (!isset($bag[$key])) {
            return new Bag();
        }

        return $bag;
    }

    /** @param array<string, int>|Bag $container */
    public function fromUnion(array|Bag $container, string $key): Bag
    {
        if (isset($container[$key]) && $container instanceof Bag) {
            return $container;
        }

        return new Bag();
    }
}

/** @implements ArrayAccess<array-key, string> */
abstract class BaseBag implements ArrayAccess
{
    public function offsetExists(mixed $offset): bool
    {
        return true;
    }

    public function offsetGet(mixed $offset): string
    {
        return '';
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    public function offsetUnset(mixed $offset): void
    {
    }
}

final class ChildBag extends BaseBag
{
}

function from_inherited(ChildBag $bag, string $key): ChildBag
{
    if (!isset($bag[$key])) {
        return new ChildBag();
    }

    return $bag;
}

/**
 * @mago-expect analysis:invalid-array-access
 * @mago-expect analysis:never-return
 */
function from_std_class(stdClass $object, string $key): stdClass
{
    if (!isset($object[$key])) {
        return new stdClass();
    }

    return $object;
}
