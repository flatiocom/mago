<?php

declare(strict_types=1);

namespace NestedWriteThroughGeneralArrayKey;

/**
 * @param array<string, array{a: int, b: int}> $m
 *
 * @return array<string, array{a: int, b: int}>
 */
function first_write(array $m, string $k): array
{
    $m[$k]['a'] = 1;

    // @mago-expect analysis:invalid-return-statement
    return $m;
}

/**
 * @param array<string, array{a: int, b: int}> $m
 *
 * @return array<string, array{a: int, b: int}>
 */
function second_write_into_same_element(array $m, string $k): array
{
    $m[$k]['a'] = 1;
    $m[$k]['b'] = 2;

    return $m;
}

/**
 * @param array<string, array<string, array{a: int, b: int}>> $m
 *
 * @return array<string, array<string, array{a: int, b: int}>>
 */
function second_write_two_levels_down(array $m, string $k, string $j): array
{
    $m[$k][$j]['a'] = 1;
    $m[$k][$j]['b'] = 2;

    return $m;
}

/**
 * @param array<string, array{a: int, b: int}> $m
 */
function second_write_keeps_other_elements(array $m, string $k): bool
{
    $m[$k]['a'] = 1;
    $m[$k]['b'] = 2;

    foreach ($m as $element) {
        if ($element['a'] === 3) {
            return true;
        }
    }

    return false;
}

/**
 * @param list<int> $years
 *
 * @return array<int, array{from: int, to: int}>
 */
function second_write_in_loop(array $years): array
{
    $dates = [];
    foreach ($years as $year) {
        $dates[$year]['from'] = 1;
        $dates[$year]['to'] = 2;
    }

    return $dates;
}

/**
 * @param array<string, array{a: int, b: int}> $m
 *
 * @return array<string, array{a: int, b: int}>
 */
function other_element_written_between(array $m, string $k, string $j): array
{
    $m[$k]['a'] = 1;
    $m[$j]['a'] = 1;
    $m[$k]['b'] = 2;

    // @mago-expect analysis:invalid-return-statement
    return $m;
}

/**
 * @param array<string, array<string, array{a: int, b: int}>> $m
 *
 * @return array<string, array<string, array{a: int, b: int}>>
 */
function sibling_written_between_two_levels_down(array $m, string $k, string $j, string $i): array
{
    $m[$k][$j]['a'] = 1;
    $m[$k][$i]['a'] = 1;
    $m[$k][$j]['b'] = 2;

    // @mago-expect analysis:invalid-return-statement
    return $m;
}

/**
 * @param array<string, array{a: int, b: int}> $m
 *
 * @return array<string, array{a: int, b: int}>
 */
function other_element_written_through_reference(array $m, string $k, string $j): array
{
    $alias = &$m;
    $m[$k]['a'] = 1;
    $alias[$j]['a'] = 1;
    $m[$k]['b'] = 2;

    // @mago-expect analysis:invalid-return-statement
    return $m;
}

/**
 * @param array<string, array{a: int, b: int}> $m
 *
 * @return array<string, array{a?: int, b: int}>
 */
function key_changed_between(array $m, string $k, string $j): array
{
    $m[$k]['a'] = 1;
    $k = $j;
    $m[$k]['b'] = 2;

    // @mago-expect analysis:invalid-return-statement
    return $m;
}

/**
 * @param array<string, array{a: int, b: int}> $m
 *
 * @return array<string, array{a?: int, b: int}>
 */
function array_added_between(array $m, string $k, string $j): array
{
    $m[$k]['a'] = 1;
    $m += [$j => ['a' => 1]];
    $m[$k]['b'] = 2;

    // @mago-expect analysis:invalid-return-statement
    return $m;
}

final class Holder
{
    /** @var array<string, array{a: int, b: int}> */
    public array $m = [];

    public function first_write(string $k): void
    {
        // @mago-expect analysis:invalid-property-assignment-value
        $this->m[$k]['a'] = 1;
    }

    public function second_write(string $k): void
    {
        // @mago-expect analysis:invalid-property-assignment-value
        $this->m[$k]['a'] = 1;
        $this->m[$k]['b'] = 2;
    }
}
