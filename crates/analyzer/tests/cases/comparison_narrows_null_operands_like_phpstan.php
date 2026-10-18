<?php

declare(strict_types=1);

final class Project
{
    public null|int $start = null;
    public null|DateTime $due = null;
}

function less_than_makes_the_greater_side_non_null(int $year, Project $project): string
{
    if ($year < $project->start) { // @mago-expect analysis:possibly-null-operand
        return 'a' . $project->start;
    }

    return 'b' . $project->start; // @mago-expect analysis:possibly-null-operand
}

function greater_than_makes_the_greater_side_non_null(int $year, null|int $start): string
{
    if ($start > $year) { // @mago-expect analysis:possibly-null-operand
        return 'a' . $start;
    }

    return 'b' . $start; // @mago-expect analysis:possibly-null-operand
}

function failed_less_or_equal_makes_the_smaller_side_non_null(Project $project, DateTime $min): DateTime
{
    if ($project->due <= new DateTime('2020-09-30')) { // @mago-expect analysis:possibly-null-operand
        return $min;
    }

    return $project->due < $min ? $min : $project->due;
}

function failed_less_than_keeps_null_on_the_greater_side(int $year, null|int $start): string
{
    if ($year < $start) { // @mago-expect analysis:possibly-null-operand
        return '';
    }

    return 'b' . $start; // @mago-expect analysis:possibly-null-operand
}

function less_or_equal_than_an_int_keeps_null(int $year, null|int $start): string
{
    if ($year <= $start) { // @mago-expect analysis:possibly-null-operand
        return 'a' . $start; // @mago-expect analysis:possibly-null-operand
    }

    return '';
}

function less_or_equal_than_an_object_drops_null(DateTime $date, null|DateTime $to): string
{
    if ($date <= $to) { // @mago-expect analysis:possibly-null-operand
        return $to->format('Y');
    }

    return '';
}

function a_literal_string_keeps_null(null|int $start): string
{
    if ('abc' < $start) { // @mago-expect analysis:possibly-null-operand
        return 'a' . $start; // @mago-expect analysis:possibly-null-operand
    }

    return '';
}

function a_failed_comparison_inside_and_says_nothing_about_the_other_operand(null|int $year, int $start): int
{
    if ($year !== null && $year < $start) {
        return 0;
    }

    if ($year === null) {
        return 1;
    }

    return $year;
}
