<?php

declare(strict_types=1);

final class CatchForgetsLookup
{
    public ?string $note = null;

    public function isReady(): bool
    {
        return true;
    }

    public function log(string $message): void
    {
        echo $message;
    }

    /**
     * @throws RuntimeException
     */
    public static function check(): bool
    {
        return true;
    }

    public function stableMethodResult(): void
    {
        if (!$this->isReady()) {
            return;
        }

        try {
            self::check();
        } catch (RuntimeException) {
            $this->log('check failed');
        }

        if (!$this->isReady()) {
            return;
        }
    }

    public function propertyFetch(): string
    {
        if ($this->note === null) {
            return '';
        }

        try {
            self::check();
        } catch (RuntimeException) {
            $this->log('check failed');
        }

        return $this->note; // @mago-expect analysis:nullable-return-statement,invalid-return-statement
    }

    public function catchThatLeaves(): void
    {
        if (!$this->isReady()) {
            return;
        }

        try {
            self::check();
        } catch (RuntimeException) {
            $this->log('check failed');

            return;
        }

        if (!$this->isReady()) { // @mago-expect analysis:impossible-condition
            return;
        }
    }
}
