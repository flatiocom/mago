<?php

declare(strict_types=1);

final class Row
{
    public int $id = 0;
}

final class Form
{
    private null|Row $row = null;
    private int $hours = 0;

    private function hoursOf(Row $row): int
    {
        return $row->id + $this->hours;
    }

    public function render(): int
    {
        if ($this->row) {
            $this->hours = $this->hoursOf($this->row);
        }

        if ($this->row) {
            return $this->row->id;
        }

        return $this->hours;
    }

    public function save(): int
    {
        if ($this->row) {
            if ($this->hours > 0) {
                $this->hours = $this->hoursOf($this->row);
            }

            return $this->row->id;
        }

        return 0;
    }
}
