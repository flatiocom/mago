<?php

declare(strict_types=1);

final class Profile
{
    public ?string $country = null;

    public ?string $lang = null;

    public function getCountry(): ?string
    {
        return $this->country;
    }
}

final class Document
{
    public ?Profile $profile = null;

    public ?string $override = null;

    /**
     * @throws LogicException
     */
    public function country(): ?string
    {
        echo $this->profile->country ?? throw new LogicException();

        return $this->profile->lang;
    }

    /**
     * @throws LogicException
     */
    public function countryThroughMethod(): ?string
    {
        echo $this->profile->getCountry() ?? throw new LogicException(); // @mago-expect analysis:possible-method-access-on-null(2)

        return $this->profile->lang; // @mago-expect analysis:possibly-null-property-access
    }

    /**
     * @throws LogicException
     */
    public function nestedCoalesce(): ?string
    {
        echo $this->override ?? $this->profile->country ?? throw new LogicException();

        return $this->profile->lang; // @mago-expect analysis:possibly-null-property-access
    }
}
