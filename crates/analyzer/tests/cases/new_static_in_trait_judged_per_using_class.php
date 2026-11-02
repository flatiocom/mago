<?php

declare(strict_types=1);

namespace NewStaticInTraitJudgedPerUsingClass;

trait Makes
{
    public static function make(): static
    {
        // @mago-expect analysis:abstract-instantiation
        return new static();
    }
}

abstract class AbstractMade
{
    use Makes;

    final public function __construct()
    {
    }
}

final class FinalMade
{
    use Makes;
}

class ChildOfAbstract extends AbstractMade
{
}

trait Builds
{
    public function build(): static
    {
        // @mago-expect analysis:unsafe-instantiation
        return new static();
    }
}

final class FinalBuilt
{
    use Builds;
}

class OpenBuilt
{
    use Builds;

    public function __construct()
    {
    }
}

trait Copies
{
    public function copy(): static
    {
        return new static();
    }
}

final class FinalCopied
{
    use Copies;
}

class SealedCopied
{
    use Copies;

    final public function __construct()
    {
    }
}
