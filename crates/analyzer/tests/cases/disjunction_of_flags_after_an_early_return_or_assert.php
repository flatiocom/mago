<?php

declare(strict_types=1);

namespace DisjunctionOfFlagsAfterAnEarlyReturnOrAssert;

/**
 * @mago-expect analysis:redundant-isset-check
 * @mago-expect analysis:redundant-condition
 */
function early_return(string $key, bool $ok): void
{
    $keys = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
    $first = in_array($key, ['a', 'b'], true) && $ok;
    $second = in_array($key, ['c', 'd'], true) && $ok;
    if (!($first || $second)) {
        return;
    }

    if (isset($keys[$key])) {
        echo $keys[$key];
    }
}

/**
 * @mago-expect analysis:redundant-isset-check
 * @mago-expect analysis:redundant-condition
 */
function asserted(string $key, bool $ok): void
{
    $keys = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
    $first = in_array($key, ['a', 'b'], true) && $ok;
    $second = in_array($key, ['c', 'd'], true) && $ok;
    assert($first || $second);
    if (isset($keys[$key])) {
        echo $keys[$key];
    }
}

function operand_implying_nothing(string $key, bool $ok): void
{
    $keys = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
    $first = in_array($key, ['a', 'b'], true) && $ok;
    if (!($first || $ok)) {
        return;
    }

    if (isset($keys[$key])) {
        echo $keys[$key];
    }
}
