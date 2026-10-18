<?php

declare(strict_types=1);

final class Row
{
}

function find_row(int $id): null|Row
{
    return $id > 0 ? new Row() : null;
}

function cutoff_after_a_null_check(null|int $months, DateTimeImmutable $today): string
{
    $cutoff = $months !== null ? $today->modify('+1 month') : null;
    if ($cutoff !== null) {
        return 'x' . $months;
    }

    return 'y' . $months; // @mago-expect analysis:possibly-null-operand
}

function nullable_then_branch(null|int $id): string
{
    $row = $id !== null ? find_row($id) : null;
    if ($row !== null) {
        return 'x' . $id;
    }

    return '';
}

/**
 * @param array{1?: non-empty-string} $matches
 */
function empty_string_else_branch(array $matches): string
{
    $estimation = isset($matches[1]) ? $matches[1] . ' MD' : '';
    if ($estimation !== '') {
        return '(' . $matches[1]; // @mago-expect analysis:possibly-undefined-int-array-index
    }

    return '';
}

function a_nullable_string_then_branch_says_nothing(null|int $id, null|string $name): string
{
    $label = $id !== null ? $name : null;
    if ($label !== null) {
        return 'x' . $id; // @mago-expect analysis:possibly-null-operand
    }

    return '';
}
