<?php

declare(strict_types=1);

namespace NullsafeChainCallCanReturnNull;

final class Team
{
    public function isLandlord(): bool
    {
        return true;
    }
}

final class Renter
{
    public ?Team $team = null;
}

final class Rent
{
    public Renter $renter;

    public function __construct()
    {
        $this->renter = new Renter();
    }
}

function landlord(?Rent $rent): bool
{
    // @mago-expect analysis:possible-method-access-on-null
    $isLandlord = $rent?->renter->team->isLandlord();

    // @mago-expect analysis:nullable-return-statement,invalid-return-statement
    return $isLandlord;
}
