<?php

declare(strict_types=1);

final class Row
{
    public int $id = 1;

    public function isOpen(): bool
    {
        return $this->id > 0;
    }
}

final class Holder
{
    private ?Row $row = null;

    public function attach(Row $row): void
    {
        $this->row = $row;
    }

    public function forgetsAfterAVoidCallInAnIf(bool $flag): ?int
    {
        if ($this->row === null) {
            return 0;
        }

        if ($flag) {
            $this->reset();
        }

        return $this->row->id; // @mago-expect analysis:possibly-null-property-access
    }

    public function forgetsAfterAVoidCallInATernary(bool $flag): ?int
    {
        if ($this->row === null) {
            return 0;
        }

        $value = $flag ? $this->resetAndCount() : 0;
        echo $value;

        return $this->row->id; // @mago-expect analysis:possibly-null-property-access
    }

    public function keepsAfterAVoidCallInALeavingBranch(bool $flag): int
    {
        if ($this->row === null) {
            return 0;
        }

        if ($flag) {
            $this->reset();

            return 0;
        }

        return $this->row->id;
    }

    /**
     * @param list<Row> $rows
     */
    public function rechecksAMethodResultForEveryRow(array $rows): int
    {
        $open = 0;
        foreach ($rows as $row) {
            if (!$row->isOpen()) {
                continue;
            }

            $open++;
        }

        return $open;
    }

    private function reset(): void
    {
    }

    /**
     * @phpstan-impure
     */
    private function resetAndCount(): int
    {
        $this->row = null;

        return 1;
    }
}
