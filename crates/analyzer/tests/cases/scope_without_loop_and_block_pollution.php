<?php

declare(strict_types=1);

function for_initialization(): int
{
    for ($i = 0, $j = 5; $i < 3; $i++) {
        echo $i;
    }

    /** @mago-expect analysis:possibly-undefined-variable */
    return $j;
}

function for_body_of_always_entered_loop(): int
{
    for ($i = 0; $i < 3; $i++) {
        $last = $i;
    }

    /** @mago-expect analysis:possibly-undefined-variable */
    return $last;
}

/**
 * @param list<int> $rows
 */
function for_initialization_of_possibly_defined_variable(array $rows, bool $flag): int
{
    if ($flag) {
        $i = 10;
    }

    for ($i = 0; $i < count($rows); $i++) {
        echo $i;
    }

    /** @mago-expect analysis:possibly-undefined-variable */
    return $i;
}

function for_initialization_of_defined_variable(): int
{
    $i = 10;
    for ($i = 0; $i < 3; $i++) {
        echo $i;
    }

    return $i;
}

function while_true_body(): int
{
    while (true) {
        $found = 1;
        break;
    }

    return $found;
}

function do_while_body(int $start): int
{
    do {
        $doubled = $start * 2;
    } while ($doubled < 0);

    return $doubled;
}

function foreach_over_non_empty_array(): int
{
    foreach ([1, 2] as $value) {
        $copy = $value;
    }

    /** @mago-expect analysis:possibly-undefined-variable */
    echo $value;

    /** @mago-expect analysis:possibly-undefined-variable */
    return $copy;
}

function foreach_over_non_empty_array_into_defined_variable(): int
{
    $sum = 0;
    foreach ([1, 2] as $value) {
        $sum = $value;
    }

    return $sum;
}

function bare_block(): int
{
    {
        $block = 1;
    }

    /** @mago-expect analysis:possibly-undefined-variable */
    return $block;
}

function bare_block_assigning_defined_variable(): int
{
    $block = 0;
    {
        $block = 1;
    }

    return $block;
}

function braced_if_and_else_bodies(int $flag): int
{
    if ($flag > 0) {
        $branch = 1;
    } elseif ($flag < 0) {
        $branch = 2;
    } else {
        $branch = 3;
    }

    return $branch;
}
