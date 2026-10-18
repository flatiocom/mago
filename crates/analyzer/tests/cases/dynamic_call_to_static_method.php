<?php

declare(strict_types=1);

trait MakesBoxes
{
    public static function fromTrait(): Box
    {
        return new Box();
    }
}

class Box
{
    use MakesBoxes;

    public static function make(): self
    {
        return new self();
    }

    public function open(): self
    {
        return $this;
    }

    public function makeFromThis(): self
    {
        // @mago-expect analysis:dynamic-static-method-call
        return $this->make();
    }
}

final class SmallBox extends Box
{
}

function call_on_instance(Box $box): Box
{
    // @mago-expect analysis:dynamic-static-method-call
    return $box->make();
}

function call_on_nullable_instance(?Box $box): ?Box
{
    // @mago-expect analysis:dynamic-static-method-call
    return $box?->make();
}

function callable_from_instance(Box $box): Closure
{
    // @mago-expect analysis:dynamic-static-method-call
    return $box->make(...);
}

function call_inherited_on_instance(SmallBox $box): Box
{
    // @mago-expect analysis:dynamic-static-method-call
    return $box->make();
}

function call_trait_method_on_instance(Box $box): Box
{
    // @mago-expect analysis:dynamic-static-method-call
    return $box->fromTrait();
}

function call_statically(Box $box): Box
{
    Box::make();

    return $box::make();
}

function call_instance_method(Box $box): Box
{
    return $box->open();
}

function bind_closure(Closure $closure, object $scope): ?Closure
{
    return $closure->bind($closure, $scope);
}
