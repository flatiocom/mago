<?php

declare(strict_types=1);

namespace ReceiverWithNonObjectPartStopsCallChecks;

final class Target
{
    public int $id = 0;

    public function take(int $value): int
    {
        return $value;
    }
}

final class Other
{
}

function nullableReceiver(?Target $target, ?int $value): int
{
    // @mago-expect analysis:possible-method-access-on-null
    return $target->take($value);
}

function nullableReceiverMissingArgument(?Target $target): int
{
    // @mago-expect analysis:possible-method-access-on-null
    return $target->take();
}

function unionWithoutTheMethod(Target|Other|null $target, int $value): int
{
    // @mago-expect analysis:possible-method-access-on-null
    return $target->take($value);
}

function unionWithoutTheProperty(Target|Other|null $target): int
{
    // @mago-expect analysis:possibly-null-property-access
    return $target->id;
}

function objectReceiverKeepsArgumentChecks(Target $target, ?int $value): int
{
    // @mago-expect analysis:possibly-null-argument
    return $target->take($value);
}

function nullsafeReceiverKeepsArgumentChecks(?Target $target, ?int $value): ?int
{
    // @mago-expect analysis:possibly-null-argument
    return $target?->take($value);
}
