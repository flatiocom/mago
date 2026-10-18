<?php

declare(strict_types=1);

final class Settings
{
    public int $touched = 0;

    public function load(): mixed
    {
        return null;
    }

    /** @return array<string, int> */
    public function counts(): array
    {
        return [];
    }

    public function touch(): void
    {
        $this->touched++;
    }

    public function takeInt(int $value): void
    {
        $this->touched = $value;
    }

    public function read(): mixed
    {
        if (isset($this->load()['a'])) { // @mago-expect analysis:mixed-array-access
            return $this->load()['a'];
        }

        if (isset($this->counts()['a'])) {
            $this->takeInt($this->counts()['a']);
        }

        return null;
    }

    public function readAfterReturn(): mixed
    {
        if (!isset($this->load()['a'])) { // @mago-expect analysis:mixed-array-access
            return null;
        }

        return $this->load()['a'];
    }

    public function readAfterAnImpureCall(): mixed
    {
        if (isset($this->load()['a'])) { // @mago-expect analysis:mixed-array-access
            $this->touch();

            return $this->load()['a']; // @mago-expect analysis:mixed-array-access
        }

        return null;
    }
}
