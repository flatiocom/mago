<?php

declare(strict_types=1);

interface ScopeContract
{
    public function limit(): ?int;
}

trait ScopeTrait
{
    public function fromTrait(): ?int
    {
        return 1;
    }

    public function callsTraitPrivate(): ?int
    {
        return $this->traitPrivate();
    }

    private function traitPrivate(): ?int // @mago-expect analysis:overly-wide-return-type
    {
        return 1;
    }

    private function traitPrivateUnion(): int|string
    {
        return 1;
    }
}

abstract class ScopeBase
{
    public function firstDeclaration(): ?int
    {
        return 1;
    }

    protected function protectedFirstDeclaration(): ?int
    {
        return 1;
    }

    public function overridden(): ?int
    {
        return null;
    }

    public function onlyNull(): ?int
    {
        return 1;
    }

    public function onlyTrue(): bool
    {
        return false;
    }

    public static function staticNullable(): ?int // @mago-expect analysis:overly-wide-return-type
    {
        return 1;
    }

    public static function staticUnion(): int|string
    {
        return 1;
    }

    public static function staticBool(): bool
    {
        if (mt_rand(0, 1)) {
            return true;
        }

        return true;
    }

    public function usesPrivates(): ?int
    {
        return $this->hidden() ?? $this->privateInBase();
    }

    private function hidden(): ?int
    {
        return $this->onlyNull();
    }

    private function privateInBase(): ?int // @mago-expect analysis:overly-wide-return-type
    {
        return 1;
    }

    final public function finalMethod(): ?int // @mago-expect analysis:overly-wide-return-type
    {
        return 1;
    }
}

class ScopeChild extends ScopeBase implements ScopeContract
{
    use ScopeTrait;

    public function overridden(): ?int // @mago-expect analysis:overly-wide-return-type
    {
        return 1;
    }

    public function onlyNull(): ?int
    {
        return null;
    }

    public function onlyTrue(): bool
    {
        if (mt_rand(0, 1)) {
            return true;
        }

        return true;
    }

    public function limit(): ?int // @mago-expect analysis:overly-wide-return-type
    {
        return 1;
    }

    public function hidden(): ?int
    {
        return 1;
    }
}

final class FinalScopeChild extends ScopeBase
{
    public function firstInFinal(): ?int // @mago-expect analysis:overly-wide-return-type
    {
        return 1;
    }

    public function onlyNull(): ?int
    {
        return null;
    }
}

enum ScopeEnum
{
    case One;

    public function label(): ?string // @mago-expect analysis:overly-wide-return-type
    {
        return 'one';
    }
}

$anonymous = new class {
    public function value(): ?int // @mago-expect analysis:overly-wide-return-type
    {
        return 1;
    }
};
