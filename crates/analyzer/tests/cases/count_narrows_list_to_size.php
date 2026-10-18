<?php

declare(strict_types=1);

function exact_count_seals_the_list(string $key): string
{
    $parts = explode(':', $key);
    if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
        return '';
    }

    /** @mago-expect analysis:undefined-int-array-index,null-operand */
    return $parts[0] . $parts[1] . $parts[2];
}

function exact_count_in_condition(string $key): string
{
    $parts = explode(':', $key);
    if (count($parts) === 3 && $parts[0] === 'range') {
        return $parts[1] . $parts[2];
    }

    return '';
}

function exact_count_on_the_left(string $key): string
{
    $parts = explode(':', $key);
    if (3 === count($parts)) {
        return $parts[2];
    }

    return '';
}

function minimum_count(string $key): string
{
    $parts = explode(':', $key);
    if (count($parts) >= 3) {
        return $parts[2];
    }

    if (count($parts) > 1) {
        return $parts[1];
    }

    return '';
}

function minimum_count_after_early_return(string $key): string
{
    $parts = explode(':', $key);
    if (count($parts) < 3) {
        return '';
    }

    return $parts[2];
}

/**
 * @param list<list<string>> $csv
 */
function exact_count_of_a_nested_list(array $csv): string
{
    if (!isset($csv[0]) || count($csv[0]) !== 3) {
        return '';
    }

    return $csv[0][2];
}

function no_count_check(string $key): string
{
    $parts = explode(':', $key);

    /** @mago-expect analysis:possibly-undefined-int-array-index */
    return $parts[1];
}
