<?php

declare(strict_types=1);

function walk_mixed_with_int_callback(mixed $values): void
{
    // @mago-expect analysis:mixed-argument
    // @mago-expect analysis:invalid-argument
    array_walk($values, function (int $value): void {
        echo $value;
    });
}

function walk_mixed_with_mixed_callback(mixed $values): void
{
    // @mago-expect analysis:mixed-argument
    array_walk($values, function (mixed $value): void {
        echo is_int($value) ? 1 : 0;
    });
}

/** @param array<string, string> $values */
function walk_typed_array(array $values): void
{
    array_walk($values, function (string $value): void {
        echo $value;
    });
}
