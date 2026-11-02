<?php

declare(strict_types=1);

/** @extends ArrayObject<int, string> */
final class TypedFlagsArrayObject extends ArrayObject
{
    public function setFlags(int $flags): void
    {
    }
}

/** @extends ArrayIterator<int, string> */
final class TypedSeekArrayIterator extends ArrayIterator
{
    public function seek(int $offset): void
    {
    }
}

abstract class UntypedUserParent
{
    abstract public function setFlags($flags): void;
}

final class TypedUserChild extends UntypedUserParent
{
    /** @mago-expect analysis:incompatible-parameter-type */
    public function setFlags(int $flags): void
    {
    }
}
