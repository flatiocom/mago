<?php

declare(strict_types=1);

function not_identical_to_true(int|bool $status): int|false
{
    if ($status !== true) {
        return $status;
    }

    return 0;
}

function true_not_identical_to(int|true $status): int
{
    if (true !== $status) {
        return $status;
    }

    return 0;
}

/**
 * @return 0|''|'0'|null
 */
function not_equal_to_true(int|string $value): null|int|string
{
    if ($value != true) {
        return $value;
    }

    return null;
}

function identical_to_true_after_the_negation(int|true $status): true
{
    if ($status !== true) {
        return true;
    }

    return $status;
}
