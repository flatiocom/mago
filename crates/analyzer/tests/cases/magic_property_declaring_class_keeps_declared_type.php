<?php

declare(strict_types=1);

final class Settings
{
}

/**
 * @property-read Settings $settings
 * @property Settings $open
 */
class User
{
    /** @var list<callable(): void> */
    public array $onLoggedIn = [];

    protected null|Settings $settings = null;

    public null|Settings $open = null;

    public function __construct()
    {
        $this->onLoggedIn[] = function (): void {
            $this->settings = null;
        };
    }

    public function reset(): void
    {
        $this->settings = null;
        $this->open = null; // @mago-expect analysis:invalid-property-assignment-value
    }

    public function read(): Settings
    {
        return $this->settings; // @mago-expect analysis:nullable-return-statement,invalid-return-statement
    }

    public function __get(string $name): mixed
    {
        return $this->settings ??= new Settings();
    }
}

class SubUser extends User
{
    public function resetFromSubclass(): void
    {
        $this->settings = null; // @mago-expect analysis:invalid-property-assignment-value
    }
}
