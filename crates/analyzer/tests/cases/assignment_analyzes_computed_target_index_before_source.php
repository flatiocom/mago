<?php

declare(strict_types=1);

final class Holder
{
    public null|int $p = null;

    private function reset(): int
    {
        $this->p = null;

        return 1;
    }

    /** @return array<int, int> */
    public function propertyIndex(): array
    {
        $b = [];
        if ($this->p) {
            $b[$this->p] = $this->reset();
        }

        return $b;
    }

    /** @return array<int, array<int, int>> */
    public function nestedPropertyIndices(): array
    {
        $e = [];
        if ($this->p) {
            $e[$this->p][$this->p] = $this->reset();
        }

        return $e;
    }

    /** @return array<int, list<int>> */
    public function propertyIndexBeforeAppend(): array
    {
        $e = [];
        if ($this->p) {
            $e[$this->p][] = $this->reset();
        }

        return $e;
    }

    /** @return array<array-key, int> */
    public function destructuringRunsSourceFirst(): array
    {
        $d = [];
        if ($this->p) {
            [$d[$this->p]] = [$this->reset()];
        }

        return $d;
    }

    /** @return array<int, string> */
    public function compoundReadsIndexFirst(): array
    {
        $b = [1 => 'a'];
        if ($this->p === 1) {
            $b[$this->p] .= (string) $this->reset();
        }

        return $b;
    }
}

/** @return array{1: 2} */
function side_effect_index_runs_before_source(): array
{
    $i = 1;
    $b = [];
    $b[$i++] = $i;

    return $b;
}

/** @return array{5: 5} */
function bare_variable_index_is_read_after_source(): array
{
    $i = 1;
    $c = [];
    $c[$i] = $i = 5;

    return $c;
}
