<?php

declare(strict_types=1);

/** @deprecated */
class DeprMembersLegacy
{
    public const LIMIT = 1;

    public int $count = 1;

    public static int $total = 1;

    public function __construct()
    {
    }

    public function run(): int
    {
        return 1;
    }

    /** @deprecated */
    public function old(): int
    {
        return 1;
    }

    public static function make(): int
    {
        return 1;
    }
}

/** @deprecated */
enum DeprMembersStatus
{
    case Active;
}

enum DeprMembersLevel
{
    /** @deprecated */
    case Old;
    case New;
}

class DeprMembersBase
{
    public const LIMIT = 1;

    public static int $total = 1;

    public static function make(): int
    {
        return 1;
    }
}

/** @deprecated */
class DeprMembersChild extends DeprMembersBase
{
}

final class DeprMembersUser
{
    public function members(DeprMembersLegacy $legacy): int
    {
        /** @mago-expect analysis:deprecated-method */
        $sum = $legacy->run();
        /** @mago-expect analysis:deprecated-method */
        $sum += $legacy->old();
        /** @mago-expect analysis:deprecated-method */
        $sum += DeprMembersLegacy::make();
        /** @mago-expect analysis:deprecated-property */
        $sum += $legacy->count;
        /** @mago-expect analysis:deprecated-property */
        $sum += DeprMembersLegacy::$total;
        /** @mago-expect analysis:deprecated-class-constant */
        $sum += DeprMembersLegacy::LIMIT;

        return $sum;
    }

    public function construction(): DeprMembersLegacy
    {
        /** @mago-expect analysis:deprecated-class,deprecated-method */
        return new DeprMembersLegacy();
    }

    public function enumCase(): DeprMembersStatus
    {
        /** @mago-expect analysis:deprecated-class-constant */
        return DeprMembersStatus::Active;
    }

    /**
     * @return list<DeprMembersLevel>
     */
    public function levels(): array
    {
        /** @mago-expect analysis:deprecated-class-constant */
        return [DeprMembersLevel::Old, DeprMembersLevel::New];
    }

    public function className(): string
    {
        /** @mago-expect analysis:deprecated-class-constant */
        return DeprMembersLegacy::class;
    }

    public function throughDeprecatedChild(): int
    {
        /** @mago-expect analysis:deprecated-method */
        $sum = DeprMembersChild::make();
        /** @mago-expect analysis:deprecated-property */
        $sum += DeprMembersChild::$total;
        /** @mago-expect analysis:deprecated-class-constant */
        $sum += DeprMembersChild::LIMIT;

        return $sum + DeprMembersBase::make() + DeprMembersBase::$total + DeprMembersBase::LIMIT;
    }
}
