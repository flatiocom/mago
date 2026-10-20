<?php

declare(strict_types=1);

/** @deprecated */
function depr_callable_legacy(): int
{
    return 1;
}

/** @deprecated */
function depr_callable_double(int $value): int
{
    return $value * 2;
}

final class DeprCallableService
{
    /** @deprecated */
    public function old(): int
    {
        return 1;
    }

    /** @deprecated */
    public static function make(): int
    {
        return 1;
    }

    /** @deprecated */
    public function __toString(): string
    {
        return '';
    }
}

final class DeprCallableUser
{
    public function callables(DeprCallableService $service): int
    {
        /** @mago-expect analysis:deprecated-function */
        $function = depr_callable_legacy(...);
        /** @mago-expect analysis:deprecated-method */
        $method = $service->old(...);
        /** @mago-expect analysis:deprecated-method */
        $static = DeprCallableService::make(...);

        /** @mago-expect analysis:deprecated-function */
        $piped = 1 |> depr_callable_double(...);

        return $function() + $method() + $static() + $piped;
    }

    public function strings(DeprCallableService $service): string
    {
        /** @mago-expect analysis:deprecated-method */
        $cast = (string) $service;

        return $cast . "{$service}";
    }
}
