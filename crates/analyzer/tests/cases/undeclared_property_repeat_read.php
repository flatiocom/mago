<?php

declare(strict_types=1);

final class Magic
{
    public function __get(string $name): mixed
    {
        return $name;
    }

    public function reads(): array
    {
        return [
            $this->foo, // @mago-expect analysis:non-documented-property
            $this->foo, // @mago-expect analysis:non-documented-property
        ];
    }

    public function reads_in_coalesce(string $key): array
    {
        return [
            (array) $this->bar['default'], // @mago-expect analysis:non-documented-property,mixed-array-access,invalid-type-cast
            (array) ($this->bar[$key] ?? []), // @mago-expect analysis:non-documented-property,mixed-array-access,invalid-type-cast
        ];
    }

    public function assigned_read_stays_silent(): mixed
    {
        $this->baz = 1; // @mago-expect analysis:non-existent-property

        return $this->baz;
    }
}

final class Plain
{
    public function reads(): array
    {
        return [
            $this->missing, // @mago-expect analysis:non-existent-property
            $this->missing, // @mago-expect analysis:non-existent-property
        ];
    }
}
