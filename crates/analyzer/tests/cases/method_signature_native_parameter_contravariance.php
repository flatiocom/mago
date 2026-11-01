<?php

declare(strict_types=1);

abstract class NativeContravarianceParent
{
    /** @param array<string, int> $values */
    abstract public function untypedParent($values): void;

    /** @param array<string, int> $values */
    abstract public function mixedChild($values): void;

    abstract public function nativeMismatch(int $value): void;

    abstract public function nativeReturn(): int;
}

final class NativeContravarianceChild extends NativeContravarianceParent
{
    /** @mago-expect analysis:incompatible-parameter-type */
    public function untypedParent(int|array $values): void
    {
    }

    public function mixedChild(mixed $values): void
    {
    }

    /** @mago-expect analysis:incompatible-parameter-type */
    public function nativeMismatch(string $value): void
    {
    }

    /** @mago-expect analysis:incompatible-return-type */
    public function nativeReturn(): string
    {
        return '';
    }
}
