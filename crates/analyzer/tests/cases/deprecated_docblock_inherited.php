<?php

declare(strict_types=1);

class DeprInheritedBase
{
    /** @deprecated */
    public const LIMIT = 1;

    /** @deprecated */
    public int $count = 1;

    /** @deprecated */
    public function old(): int
    {
        return 1;
    }

    /** @deprecated */
    public function kept(): int
    {
        return 1;
    }

    #[\Deprecated]
    public function flagged(): int
    {
        return 1;
    }
}

class DeprInheritedChild extends DeprInheritedBase
{
    public const LIMIT = 2;

    public int $count = 2;

    /** @return int */
    public function old(): int
    {
        return 2;
    }

    /** @not-deprecated */
    public function kept(): int
    {
        return 2;
    }

    public function flagged(): int
    {
        return 2;
    }
}

class DeprInheritedGrandChild extends DeprInheritedChild
{
    public function old(): int
    {
        return 3;
    }
}

final class DeprInheritedUser
{
    public function run(DeprInheritedChild $child, DeprInheritedGrandChild $grandChild): int
    {
        /** @mago-expect analysis:deprecated-method */
        $sum = $child->old();
        /** @mago-expect analysis:deprecated-method */
        $sum += $grandChild->old();
        /** @mago-expect analysis:deprecated-property */
        $sum += $child->count;
        /** @mago-expect analysis:deprecated-class-constant */
        $sum += DeprInheritedChild::LIMIT;

        return $sum + $child->kept() + $child->flagged();
    }
}
