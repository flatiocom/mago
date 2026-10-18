<?php

declare(strict_types=1);

/**
 * @param array<string, array<string, string>> $by_evidence
 *
 * @return list<list<string>>
 */
function grouped_values(array $by_evidence): array
{
    return array_values(array_map(array_values(...), $by_evidence));
}

/** @param list<list<string>> $grouped */
function take_grouped(array $grouped): void
{
}

/** @param array<string, array<non-empty-string, non-empty-string>> $by_evidence */
function pass_grouped(array $by_evidence): void
{
    take_grouped(array_values(array_map(array_values(...), $by_evidence)));
}

/**
 * @param array<string, array<string, string>> $by_evidence
 *
 * @return list<list<int>>
 */
function grouped_values_as_ints(array $by_evidence): array
{
    /** @mago-expect analysis:invalid-return-statement */
    return array_values(array_map(array_values(...), $by_evidence));
}

/**
 * @param list<int> $numbers
 *
 * @return list<list<mixed>>
 */
function values_of_numbers(array $numbers): array
{
    /** @mago-expect analysis:invalid-argument */
    return array_map(array_values(...), $numbers);
}
