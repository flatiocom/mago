<?php

declare(strict_types=1);

/**
 * @param array<string, int> $totals
 */
function default_fails_the_comparison(array $totals, string $period): int
{
    if (($totals[$period] ?? 0) > 0) {
        return $totals[$period];
    }

    return 0;
}

/**
 * @param array<string, int> $totals
 */
function default_fails_on_the_right(array $totals, string $period): int
{
    if (5 === ($totals[$period] ?? 0)) {
        return $totals[$period];
    }

    return 0;
}

/**
 * @param array<string, int> $totals
 * @param list<string> $periods
 *
 * @return list<float|null>
 */
function default_fails_in_a_ternary(array $totals, array $periods): array
{
    return array_map(
        static fn(string $period): null|float => ($totals[$period] ?? 0) > 0 ? 100 / $totals[$period] : null,
        $periods,
    );
}

/**
 * @param array<string, int> $totals
 */
function default_passes_and_the_negation_narrows(array $totals, string $period): int
{
    if (($totals[$period] ?? 0) <= 0) {
        return 0;
    }

    return $totals[$period];
}

/**
 * @param array<string, string> $names
 */
function default_passes_an_identity(array $names, string $key): string
{
    if (($names[$key] ?? '') === '') {
        return '';
    }

    return $names[$key];
}

function plain_variable_loses_null(?int $count): int
{
    if (($count ?? 0) > 0) {
        return $count;
    }

    return 0;
}

/**
 * @param array<string, int> $totals
 */
function the_branch_the_default_can_take_stays_unknown(array $totals, string $period): ?int
{
    if (($totals[$period] ?? 0) > 0) {
        return null;
    }

    return $totals[$period]; // @mago-expect analysis:possibly-undefined-string-array-index
}

/**
 * @param array<string, int> $totals
 */
function default_that_passes_narrows_nothing(array $totals, string $period): ?int
{
    if (($totals[$period] ?? 0) > -1) {
        return $totals[$period]; // @mago-expect analysis:possibly-undefined-string-array-index
    }

    return null;
}

/**
 * @param array<string, int> $totals
 */
function non_literal_operand_narrows_nothing(array $totals, string $period, int $minimum): ?int
{
    if (($totals[$period] ?? 0) > $minimum) {
        return $totals[$period]; // @mago-expect analysis:possibly-undefined-string-array-index
    }

    return null;
}

function key_exists_but_null_stays(?string $description, string $added): string
{
    return ($description ?? '') === ''
        ? $added
        : $description . ', ' . $added; // @mago-expect analysis:possibly-null-operand
}
