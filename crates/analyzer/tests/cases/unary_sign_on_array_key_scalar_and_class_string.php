<?php

declare(strict_types=1);

namespace UnarySignOnArrayKeyScalarAndClassString;

/** @param array<mixed> $values */
function minus_on_array_key(array $values): mixed
{
    foreach ($values as $key => $_) {
        // @mago-expect analysis:possibly-invalid-operand
        return -$key;
    }

    return 0;
}

/** @param array<mixed> $values */
function plus_on_array_key(array $values): mixed
{
    foreach ($values as $key => $_) {
        // @mago-expect analysis:possibly-invalid-operand
        return +$key;
    }

    return 0;
}

/** @param scalar $value */
function minus_on_scalar(mixed $value): mixed
{
    // @mago-expect analysis:possibly-invalid-operand
    return -$value;
}

/** @param class-string $value */
function plus_on_class_string(string $value): mixed
{
    // @mago-expect analysis:invalid-operand
    return +$value;
}

/** @param array<int, mixed> $values */
function minus_on_int_key(array $values): int
{
    foreach ($values as $key => $_) {
        return -$key;
    }

    return 0;
}

/** @param numeric $value */
function minus_on_numeric(mixed $value): int|float
{
    return -$value;
}
