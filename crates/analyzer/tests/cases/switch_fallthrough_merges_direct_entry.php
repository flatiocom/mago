<?php

declare(strict_types=1);

function switch_fallthrough_takes_int(int $value): int
{
    return $value;
}

final class SwitchFallthroughMinifier
{
    public const int KeepA = 1;
    public const int DeleteA = 2;
    public const int DeleteAB = 3;

    private string $current = '';

    public function middleCaseSeesDirectEntry(int $command, int|string $value): void
    {
        switch ($command) {
            case self::KeepA:
                $value = 1;

            case self::DeleteA:
                switch_fallthrough_takes_int($value); // @mago-expect analysis:possibly-invalid-argument

            case self::DeleteAB:
                $this->current = 'b';
        }
    }

    public function lastCaseSeesDirectEntry(int $command, int|string $value): void
    {
        switch ($command) {
            case self::KeepA:
                $value = 1;

            case self::DeleteA:
                switch_fallthrough_takes_int($value); // @mago-expect analysis:possibly-invalid-argument
        }
    }

    public function current(): string
    {
        return $this->current;
    }
}
