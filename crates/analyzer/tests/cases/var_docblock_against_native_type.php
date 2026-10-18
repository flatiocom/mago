<?php

declare(strict_types=1);

namespace Fixture;

class Row
{
}

final class UserRow extends Row
{
}

class Selection
{
    public function where(string $condition): static
    {
        echo $condition;

        return $this;
    }
}

final class GroupedSelection extends Selection
{
}

final class Repository
{
    public function row(): Row
    {
        return new UserRow();
    }

    public function maybeRow(): null|Row
    {
        return null;
    }

    public function related(): GroupedSelection
    {
        return new GroupedSelection();
    }

    /**
     * @return list<int>
     */
    public function ids(): array
    {
        return [1];
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * @return int
     */
    public function untyped()
    {
        return 1;
    }
}

function count_rows(): int
{
    return 1;
}

/**
 * @mago-expect analysis:docblock-type-mismatch
 */
function literal_outside_native_type(): string
{
    /** @var string $value */
    $value = 1;

    return $value;
}

/**
 * @mago-expect analysis:docblock-type-mismatch
 */
function empty_array_as_object(): Row
{
    /** @var Row $row */
    $row = [];

    return $row;
}

/**
 * @mago-expect analysis:docblock-type-mismatch
 */
function method_widened_with_null(Repository $repository): null|Row
{
    /** @var Row|null $row */
    $row = $repository->row();

    return $row;
}

/**
 * @mago-expect analysis:docblock-type-mismatch
 */
function method_widened_to_parent(Repository $repository): Selection
{
    /** @var Selection $selection */
    $selection = $repository->related()->where('active');

    return $selection;
}

/**
 * @mago-expect analysis:docblock-type-mismatch
 */
function static_method_outside_native_type(): Row
{
    /** @var Row $row */
    $row = Repository::create();

    return $row;
}

/**
 * @mago-expect analysis:docblock-type-mismatch
 */
function function_outside_native_type(): string
{
    /** @var string $count */
    $count = count_rows();

    return $count;
}

function literal_narrowed(): int
{
    /** @var positive-int $value */
    $value = 1;

    return $value;
}

/**
 * @return list<string>
 */
function empty_array_narrowed(): array
{
    /** @var list<string> $names */
    $names = [];

    return $names;
}

function method_narrowed(Repository $repository): null|UserRow
{
    /** @var UserRow|null $row */
    $row = $repository->maybeRow();

    return $row;
}

function nullsafe_method_narrowed(null|Repository $repository): null|UserRow
{
    /** @var UserRow|null $row */
    $row = $repository?->row();

    return $row;
}

function static_return_bound_to_receiver(Selection $selection): Selection
{
    /** @var Selection $filtered */
    $filtered = $selection->where('active');

    return $filtered;
}

/**
 * @return list<int>
 */
function method_array_narrowed(Repository $repository): array
{
    /** @var list<positive-int> $ids */
    $ids = $repository->ids();

    return $ids;
}

function untyped_method_has_no_native_type(Repository $repository): int
{
    /** @var positive-int $value */
    $value = $repository->untyped();

    return $value;
}

function instantiation_narrowed(): UserRow
{
    /** @var UserRow $row */
    $row = new Row();

    return $row;
}
