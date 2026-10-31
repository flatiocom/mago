<?php

declare(strict_types=1);

namespace InArrayClassStringValuesLikePhpstan;

final class First
{
}

final class Second
{
}

/**
 * @param First::class|Second::class $class
 * @param 'InArrayClassStringValuesLikePhpstan\First'|'InArrayClassStringValuesLikePhpstan\Second' $name
 * @param First::class $first
 *
 * @mago-expect analysis:redundant-type-comparison(2)
 * @mago-expect analysis:impossible-type-comparison
 */
function class_strings(string $class, string $name, string $first): void
{
    if (in_array($class, [First::class, Second::class], true)) {
        echo 1;
    }

    if (in_array($name, [First::class, Second::class], true)) {
        echo 2;
    }

    if (in_array($first, ['InArrayClassStringValuesLikePhpstan\first'], true)) {
        echo 3;
    }
}

/**
 * @param class-string<First>|class-string<Second> $class
 */
function generic_class_strings(string $class): void
{
    if (in_array($class, [First::class, Second::class], true)) {
        echo 1;
    }
}
