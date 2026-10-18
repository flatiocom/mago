<?php

declare(strict_types=1);

namespace Fixture;

final class Service {}

final class Container
{
    /**
     * @template T of object
     *
     * @param class-string<T> $type
     *
     * @return ($throw is true ? T : T|null)
     */
    public function getByType(string $type, bool $throw = true): ?object
    {
        return null;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $type
     */
    public function has(string $type): bool
    {
        return $type !== '';
    }

    /**
     * @template T of object = Service
     *
     * @param class-string<T> $type
     *
     * @return T|null
     */
    public function withDefault(string $type): ?object
    {
        return null;
    }

    /**
     * @template T of object
     *
     * @return T|null
     */
    public function onlyInReturn(): ?object
    {
        return null;
    }
}

/**
 * @template T
 *
 * @param list<T> $items
 *
 * @return T|null
 */
function first_item(array $items): mixed
{
    return $items[0] ?? null;
}

function resolves(Container $container, string $name): void
{
    $container->getByType(Service::class);
    // @mago-expect analysis:possibly-invalid-argument
    $container->has($name);
    // @mago-expect analysis:possibly-invalid-argument
    $container->withDefault($name);
    $container->onlyInReturn();
    first_item([1, 2]);
}

function cannot_resolve(Container $container, string $name): ?object
{
    // @mago-expect analysis:unresolvable-template-type
    // @mago-expect analysis:possibly-invalid-argument
    return $container->getByType($name);
}

/**
 * @template T of object
 *
 * @param class-string<T>|null $type
 *
 * @return T|null
 */
function make_optional(?string $type = null): ?object
{
    return null;
}

function cannot_resolve_omitted_or_mixed(mixed $type): void
{
    // @mago-expect analysis:unresolvable-template-type
    make_optional();
    // @mago-expect analysis:unresolvable-template-type
    // @mago-expect analysis:mixed-argument
    make_optional($type);
}

final class Form
{
    /**
     * @template T of object
     *
     * @param class-string<T>|T|'array'|null $returnType
     *
     * @return ($returnType is class-string<T>|T ? T : ($returnType is 'array' ? mixed[] : object))
     */
    public function getValues(string|object|null $returnType = null): object|array
    {
        return [];
    }
}

/**
 * @template K of array-key
 * @template V
 *
 * @param array<K, V|null> $array
 *
 * @return array<K, V>
 */
function filter_nulls(array $array): array
{
    return [];
}

/**
 * @param array<string, mixed> $values
 */
function branch_without_template(Form $form, array $values): void
{
    $form->getValues();
    $form->getValues('array');
    $form->getValues(Service::class);
    filter_nulls($values);
}

/**
 * @template K of array-key
 * @template V
 *
 * @param array<K, V> $items
 *
 * @return array<K, V>
 */
function copy_items(array $items): array
{
    return $items;
}

/**
 * @param iterable<string, int> $items
 *
 * @return array<string, int>
 */
function copy_iterable(iterable $items): array
{
    // @mago-expect analysis:possibly-invalid-argument
    return copy_items($items);
}
