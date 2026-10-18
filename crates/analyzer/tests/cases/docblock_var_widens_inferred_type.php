<?php

declare(strict_types=1);

/**
 * @return int
 */
function phpdoc_int()
{
    return 1;
}

/**
 * @return int|string
 */
function phpdoc_int_or_string()
{
    return 1;
}

class Animal
{
}

class Dog extends Animal
{
}

/**
 * @template-covariant T of Animal
 */
final class Shelter
{
    /**
     * @var list<T>
     */
    public array $animals = [];
}

class Builder
{
    /**
     * @return static
     */
    public function with(): static
    {
        return $this;
    }

    public function widenStaticOfThis(): Builder
    {
        // @mago-expect analysis:docblock-type-mismatch
        /** @var Builder $builder */
        $builder = $this->with();

        return $builder;
    }
}

/**
 * @return Shelter<Animal>
 */
function animal_shelter(): Shelter
{
    return new Shelter();
}

function widen_call_result(): int|string
{
    // @mago-expect analysis:docblock-type-mismatch
    /** @var int|string $value */
    $value = phpdoc_int();

    return $value;
}

function narrow_call_result(): int
{
    /** @var int $value */
    $value = phpdoc_int_or_string();

    return $value;
}

function widen_literal(): int|string
{
    /** @var int|string $value */
    $value = 1;

    return $value;
}

/**
 * @return array<array-key, int>
 */
function widen_array_literal(): array
{
    /** @var array<array-key, int> $value */
    $value = [1, 2];

    return $value;
}

function resolve_static_of_other_receiver(Builder $builder): Builder
{
    /** @var Builder $built */
    $built = $builder->with();

    return $built;
}

/**
 * @return Shelter<Dog>
 */
function narrow_covariant_template(): Shelter
{
    /** @var Shelter<Dog> $shelter */
    $shelter = animal_shelter();

    return $shelter;
}
