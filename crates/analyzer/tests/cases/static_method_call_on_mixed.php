<?php

declare(strict_types=1);

final class Factory
{
    public static function make(): self
    {
        return new self();
    }
}

function call_on_mixed(mixed $class): mixed
{
    // @mago-expect analysis:mixed-method-access
    return $class::make();
}

function partial_on_mixed(mixed $class): mixed
{
    // @mago-expect analysis:mixed-method-access
    return $class::make(...);
}

/**
 * @param class-string<Factory> $class
 */
function call_on_class_string(string $class): Factory
{
    return $class::make();
}

function call_on_object(Factory $factory): Factory
{
    return $factory::make();
}

function call_on_narrowed_mixed(mixed $value): ?Factory
{
    if ($value instanceof Factory) {
        return $value::make();
    }

    return null;
}
