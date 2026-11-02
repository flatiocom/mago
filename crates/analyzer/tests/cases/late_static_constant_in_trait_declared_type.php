<?php

declare(strict_types=1);

namespace LateStaticConstantInTraitDeclaredType;

function take_int(int $value): int
{
    return $value;
}

trait ReadsUntyped
{
    public function read(): int
    {
        // @mago-expect analysis:mixed-array-access,mixed-argument
        return take_int(static::UNTYPED['a']);
    }
}

class OpenUser
{
    use ReadsUntyped;

    public const UNTYPED = ['a' => 1];
}

trait ReadsTyped
{
    public function read(): int
    {
        return take_int(static::TYPED['a']);
    }
}

class OpenTypedUser
{
    use ReadsTyped;

    /** @var array{a: int} */
    public const array TYPED = ['a' => 1];
}

trait ReadsFinal
{
    public function read(): int
    {
        return take_int(static::UNTYPED['a']);
    }
}

final class FinalUser
{
    use ReadsFinal;

    public const UNTYPED = ['a' => 1];
}
