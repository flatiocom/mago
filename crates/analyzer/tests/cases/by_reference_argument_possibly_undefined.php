<?php

declare(strict_types=1);

function by_reference_argument_in_condition(bool $flag, string $value): void
{
    if ($flag) {
        $match = [];
    }

    if ($flag && preg_match('~(\d)~', $value, $match) === 1) {
        echo count($match);
    }
}

function by_reference_argument_after_conditional_assignment(bool $flag, string $value): void
{
    if ($flag) {
        $match = [];
    }

    preg_match('~(\d)~', $value, $match);
    echo count($match);
}

function by_reference_variable_after_conditional_assignment(bool $flag): void
{
    if ($flag) {
        $value = 1;
    }

    $reference = &$value;
    echo (string) $reference;
}

/**
 * @param array<int, string> $groups
 */
function by_reference_array_element_with_possibly_undefined_index(bool $flag, string $value, array $groups): void
{
    if (!isset($groups[1])) {
        return;
    }

    if ($flag) {
        $index = 1;
    }

    /** @mago-expect analysis:possibly-undefined-variable */
    preg_match('~(\d)~', $value, $groups[$index]);
}

function read_after_conditional_assignment_still_reported(bool $flag): void
{
    if ($flag) {
        $value = 1;
    }

    /** @mago-expect analysis:possibly-undefined-variable */
    echo (string) $value;
}
