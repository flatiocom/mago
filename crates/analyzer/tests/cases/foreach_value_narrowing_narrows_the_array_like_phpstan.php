<?php

declare(strict_types=1);

final class Labels
{
    public const array FIELDS = ['a' => 'A', 'b' => 'B'];
}

/**
 * @param list<array{field: string}> $changes
 */
function isset_in_an_earlier_loop_narrows_the_array(array $changes): string
{
    foreach ($changes as $change) {
        if (!isset(Labels::FIELDS[$change['field']])) {
            return '';
        }
    }

    $labels = '';
    foreach ($changes as $change) {
        $labels .= Labels::FIELDS[$change['field']];
    }

    return $labels;
}

/**
 * @param list<int|null> $counts
 */
function early_return_narrows_the_elements(array $counts): int
{
    foreach ($counts as $count) {
        if ($count === null) {
            return 0;
        }
    }

    $sum = 0;
    foreach ($counts as $count) {
        $sum += $count;
    }

    return $sum;
}

/**
 * @param list<int|null> $counts
 */
function break_keeps_the_elements(array $counts): int
{
    foreach ($counts as $count) {
        if ($count === null) {
            break;
        }
    }

    $sum = 0;
    foreach ($counts as $count) {
        $sum += $count; // @mago-expect analysis:possibly-null-operand
    }

    return $sum;
}

/**
 * @param list<int|null> $counts
 */
function continue_keeps_the_elements(array $counts): int
{
    foreach ($counts as $count) {
        if ($count === null) {
            continue;
        }
    }

    $sum = 0;
    foreach ($counts as $count) {
        $sum += $count; // @mago-expect analysis:possibly-null-operand
    }

    return $sum;
}

/**
 * @param list<int|null> $counts
 */
function reassigned_value_keeps_the_elements(array $counts): int
{
    foreach ($counts as $count) {
        if ($count === null) {
            return 0;
        }

        $count = null;
    }

    $sum = 0;
    foreach ($counts as $count) {
        $sum += $count; // @mago-expect analysis:possibly-null-operand
    }

    return $sum;
}

/**
 * @param array{first: int|null, second: int|null} $counts
 */
function sealed_shape_keeps_its_items(array $counts): int
{
    foreach ($counts as $count) {
        if ($count === null) {
            return 0;
        }
    }

    return $counts['first'] + 1; // @mago-expect analysis:possibly-null-operand
}
