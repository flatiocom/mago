<?php

declare(strict_types=1);

/**
 * @return 0|1|false
 *
 * @psalm-assert-if-true non-empty-string $value
 * @psalm-assert-if-false null $value
 */
function check(?string $value): int|false
{
    return $value === null ? 0 : 1;
}

/**
 * @psalm-assert-if-true non-empty-string $value
 */
function count_matches(?string $value): int
{
    return $value === null ? 0 : 2;
}

function identical(?string $value): string
{
    if (check($value) === 1) {
        return $value;
    }

    return '';
}

function identical_on_the_left(?string $value): string
{
    if (1 === check($value)) {
        return $value;
    }

    return '';
}

function not_identical(?string $value): string
{
    if (check($value) !== 1) {
        return '';
    }

    return $value;
}

function not_identical_branch(?string $value): null
{
    if (check($value) !== 1) {
        return $value;
    }

    return null;
}

/** @mago-expect analysis:nullable-return-statement,invalid-return-statement */
function identical_to_a_falsy_value(?string $value): string
{
    if (check($value) === 0) {
        return $value;
    }

    return '';
}

/** @mago-expect analysis:nullable-return-statement,invalid-return-statement */
function identical_with_other_truthy_values(?string $value): string
{
    if (count_matches($value) === 1) {
        return $value;
    }

    return '';
}
