<?php

declare(strict_types=1);

namespace Fixture;

class Row
{
}

class UserRow extends Row
{
}

/**
 * @template-covariant T of Row
 */
class Selection
{
    /** @var list<T> */
    public array $rows = [];
}

/**
 * @template K of array-key
 * @template V
 */
class Map
{
    /** @var array<K, V> */
    public array $entries = [];
}

/**
 * @template T of Row
 * @template S of Selection<T>
 */
class View
{
    /**
     * @param T $row
     * @param S $selection
     */
    public function __construct(
        public readonly Row $row,
        public readonly Selection $selection,
    ) {}
}

interface Repository
{
    /**
     * @param Selection<mixed> $selection
     *
     * @mago-expect analysis:invalid-template-parameter
     */
    public function count(Selection $selection): int;
}

final class InMemoryRepository implements Repository
{
    public function count(Selection $selection): int
    {
        return count($selection->rows);
    }
}

final class Helpers
{
    /**
     * @param Selection<mixed> $selection
     *
     * @mago-expect analysis:invalid-template-parameter
     */
    public static function mixedArgument(Selection $selection): void
    {
    }

    /**
     * @template T
     *
     * @param Selection<T> $selection
     *
     * @mago-expect analysis:invalid-template-parameter
     */
    public static function unboundedTemplate(Selection $selection): void
    {
    }

    /**
     * @param list<Selection<string>> $selections
     *
     * @mago-expect analysis:invalid-template-parameter
     */
    public static function nestedArgument(array $selections): void
    {
    }

    /**
     * @param Map<float, int> $map
     *
     * @mago-expect analysis:invalid-template-parameter
     */
    public static function arrayKeyBound(Map $map): void
    {
    }

    /**
     * @param View<UserRow, Selection<Row>> $view
     *
     * @mago-expect analysis:invalid-template-parameter
     */
    public static function boundOnAnotherTemplate(View $view): void
    {
    }

    /**
     * @template T of Row
     *
     * @param Selection<T> $selection
     * @param Selection<UserRow> $users
     * @param Selection<Row|UserRow>|null $rows
     * @param Map<string, mixed> $map
     * @param View<UserRow, Selection<UserRow>> $view
     */
    public static function withinBounds(Selection $selection, Selection $users, ?Selection $rows, Map $map, View $view): void
    {
    }
}

/**
 * @param Selection<int> $selection
 *
 * @mago-expect analysis:invalid-template-parameter
 */
function scalar_argument(Selection $selection): void
{
}
