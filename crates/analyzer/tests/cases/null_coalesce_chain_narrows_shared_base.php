<?php

declare(strict_types=1);

final class CoalesceProfile
{
    public ?string $homeCountry = null;
}

final class CoalesceAccount
{
    public ?string $country = null;

    public ?CoalesceProfile $profile = null;

    public function isCompany(): bool
    {
        return true;
    }
}

final class CoalesceOrder
{
    public ?CoalesceAccount $account = null;

    public function currency(): string
    {
        return 'EUR';
    }
}

final class CoalesceController
{
    private ?CoalesceOrder $order = null;

    public function setOrder(CoalesceOrder $order): void
    {
        $this->order = $order;
    }

    /**
     * @throws RuntimeException
     */
    public function bothSidesNarrowTheBase(): string
    {
        $country = $this->order->account->country
            ?? $this->order->account->profile->homeCountry
            ?? throw new RuntimeException('No country.');

        return $this->order->currency() . $country . ($this->order->account->isCompany() ? '1' : '0');
    }

    public function fallbackKeepsTheBaseNullable(): string
    {
        $country = $this->order->account->country ?? 'xx';

        return $this->order->currency() . $country; // @mago-expect analysis:possible-method-access-on-null
    }
}
