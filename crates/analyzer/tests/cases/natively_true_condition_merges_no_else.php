<?php

declare(strict_types=1);

namespace NativelyTrueConditionMergesNoElse;

/**
 * @mago-expect analysis:redundant-comparison
 * @mago-expect analysis:unevaluated-code
 */
function native_literal(): void
{
    $value = 'a';
    if ($value === 'a') {
        return;
    }

    echo 1;
}

/**
 * @mago-expect analysis:redundant-type-comparison
 * @mago-expect analysis:unevaluated-code
 */
function native_while(): void
{
    $value = 'a';
    while ($value === 'a') {
        return;
    }

    echo 1;
}

/**
 * @param 'a' $value
 */
function docblock_literal(string $value): void
{
    if ($value === 'a') {
        return;
    }

    echo 1;
}

/**
 * @mago-expect analysis:redundant-type-comparison
 */
function in_array_with_native_bool(): void
{
    if (in_array('a', ['a', 'b'], true)) {
        return;
    }

    echo 1;
}

/**
 * @mago-expect analysis:redundant-type-comparison
 */
function while_in_array_with_native_bool(): void
{
    while (in_array('a', ['a', 'b'], true)) {
        return;
    }

    echo 1;
}
