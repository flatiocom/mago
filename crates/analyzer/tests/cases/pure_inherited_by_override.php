<?php

declare(strict_types=1);

interface PureInheritedSource
{
    /** @pure */
    public function value(): int;
}

class PureInheritedBase implements PureInheritedSource
{
    public function value(): int
    {
        return 1;
    }
}

final class PureInheritedImpure extends PureInheritedBase
{
    public function value(): int
    {
        // @mago-expect analysis:impure-construct
        echo 1;

        return 2;
    }
}

class PureInheritedOptOutBase
{
    /** @pure */
    public function value(): int
    {
        return 1;
    }
}

final class PureInheritedOptOut extends PureInheritedOptOutBase
{
    /** @impure */
    public function value(): int
    {
        echo 1;

        return 2;
    }
}

class PureInheritedConstructorBase
{
}

final class PureInheritedConstructor extends PureInheritedConstructorBase
{
    public function __construct()
    {
        echo 1;
    }
}
