<?php

declare(strict_types=1);

namespace RedundantCheckOnTraitPropertyInitializedByConstructor;

trait Counts
{
    // @mago-expect analysis:uninitialized-property
    private int $count;
}

trait SharesCount
{
    protected int $shared;
}

trait CountsAgain
{
    use Counts;
}

final class Counter
{
    use Counts;

    public function __construct()
    {
        $this->count = 1;
    }

    public function count(): int
    {
        // @mago-expect analysis:redundant-null-coalesce
        return $this->count ?? 0;
    }

    public function isCounted(): bool
    {
        // @mago-expect analysis:redundant-isset-check
        return isset($this->count);
    }
}

final class NestedCounter
{
    use CountsAgain;

    public function __construct()
    {
        $this->count = 1;
    }

    public function count(): int
    {
        // @mago-expect analysis:redundant-null-coalesce
        return $this->count ?? 0;
    }
}

class BaseCounter
{
    use SharesCount;

    public function __construct()
    {
        $this->shared = 1;
    }
}

final class ChildCounter extends BaseCounter
{
    public function count(): int
    {
        return $this->shared ?? 0;
    }
}

final class BranchCounter
{
    use Counts;

    public function __construct(bool $flag)
    {
        if ($flag) {
            $this->count = 1;
        }
    }

    public function count(): int
    {
        return $this->count ?? 0;
    }
}
