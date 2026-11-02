<?php

declare(strict_types=1);

namespace DeadCatchKeepsTryInitializations;

final class Counter
{
    private int $assigned;
    // @mago-expect analysis:uninitialized-property
    private int $risky;

    public function __construct(bool $flag)
    {
        try {
            $this->assigned = 1;
        } catch (\RuntimeException) { // @mago-expect analysis:dead-catch
        }

        try {
            $this->risky = $this->compute($flag);
        } catch (\RuntimeException) {
        }
    }

    public function assigned(): int
    {
        // @mago-expect analysis:redundant-null-coalesce
        return $this->assigned ?? 0;
    }

    public function risky(): int
    {
        return $this->risky ?? 0;
    }

    private function compute(bool $flag): int
    {
        if ($flag) {
            throw new \RuntimeException();
        }

        return 1;
    }
}
