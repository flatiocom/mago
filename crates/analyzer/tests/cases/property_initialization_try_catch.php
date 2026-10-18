<?php

declare(strict_types=1);

final class CatchSwallows
{
    // @mago-expect analysis:uninitialized-property
    private string $client;

    public function __construct()
    {
        try {
            $this->client = self::connect();
        } catch (RuntimeException $e) {
            error_log($e->getMessage());
        }
    }

    public function client(): string
    {
        return $this->client;
    }

    private static function connect(): string
    {
        return 'client';
    }
}

final class CatchRethrows
{
    private string $client;

    /** @throws LogicException */
    public function __construct()
    {
        try {
            $this->client = self::connect();
        } catch (RuntimeException $e) {
            throw new LogicException($e->getMessage());
        }
    }

    public function client(): string
    {
        return $this->client;
    }

    private static function connect(): string
    {
        return 'client';
    }
}

final class CatchAssignsToo
{
    private ?string $client;

    public function __construct()
    {
        try {
            $this->client = self::connect();
        } catch (RuntimeException) {
            $this->client = null;
        }
    }

    public function client(): ?string
    {
        return $this->client;
    }

    private static function connect(): string
    {
        return 'client';
    }
}

final class TryAlwaysLeaves
{
    private string $client;

    public function __construct()
    {
        try {
            self::fail();
        } catch (RuntimeException) {
            $this->client = 'fallback';
        }
    }

    public function client(): string
    {
        return $this->client;
    }

    /** @throws RuntimeException */
    private static function fail(): never
    {
        throw new RuntimeException('down');
    }
}

final class FinallyAssigns
{
    private string $client;

    public function __construct()
    {
        try {
            self::connect();
        } catch (RuntimeException $e) {
            error_log($e->getMessage());
        } finally {
            $this->client = 'client';
        }
    }

    public function client(): string
    {
        return $this->client;
    }

    private static function connect(): void
    {
    }
}
