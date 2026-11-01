<?php

declare(strict_types=1);

namespace AlwaysTrueConditionMergesNoElseLikePhpstan;

/**
 * @mago-expect analysis:redundant-type-comparison
 * @mago-expect analysis:unevaluated-code
 */
function returns_on_in_array(): void
{
    if (in_array('a', ['a', 'b'], true)) {
        return;
    }

    echo 1;
}

/**
 * @mago-expect analysis:impossible-type-comparison
 * @mago-expect analysis:unevaluated-code
 */
function returns_unless_in_array(): void
{
    if (!in_array('c', ['a', 'b'], true)) {
        return;
    }

    echo 1;
}

/**
 * @throws \LogicException
 *
 * @mago-expect analysis:unevaluated-code
 */
function closing_throw(): void
{
    if (in_array('a', ['a', 'b'], true)) {
        return;
    }

    throw new \LogicException('none');
}

/**
 * @mago-expect analysis:redundant-type-comparison
 * @mago-expect analysis:unevaluated-code
 */
function else_that_falls_through(): int
{
    if (in_array('a', ['a', 'b'], true)) {
        return 1;
    } else {
        $value = 2;
    }

    return $value;
}

/**
 * @mago-expect analysis:redundant-type-comparison
 */
function assigned_inside(): string
{
    if (in_array('a', ['a', 'b'], true)) {
        $name = 'name';
    }

    return $name;
}

/**
 * @mago-expect analysis:redundant-type-comparison
 * @mago-expect analysis:unevaluated-code
 */
function while_on_in_array(): void
{
    while (in_array('a', ['a', 'b'], true)) {
        return;
    }

    echo 1;
}

/**
 * @param 'a'|'c' $needle
 */
function needle_maybe_missing(string $needle): void
{
    if (in_array($needle, ['a', 'b'], true)) {
        return;
    }

    echo 1;
}

/**
 * @param 'a'|'c' $needle
 */
function while_maybe_entered(string $needle): void
{
    while (in_array($needle, ['a', 'b'], true)) {
        return;
    }

    echo 1;
}
