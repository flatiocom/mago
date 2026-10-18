<?php

declare(strict_types=1);

/**
 * @return array<int, float>
 */
function empty_months(): array
{
    return array_fill(1, 12, 0.0);
}

/**
 * @param list<string> $names
 * @param 1|2|3 $month
 *
 * @return array<string, array{visits: array<int, float>}>
 */
function nested_write_into_a_key_that_may_be_missing(array $names, int $month): array
{
    $totals = [];
    foreach ($names as $name) {
        if (!isset($totals[$name])) {
            $totals[$name] = ['visits' => empty_months()];
        }
    }

    foreach (array_keys($totals) as $name) {
        /** @mago-expect analysis:possibly-undefined-string-array-index */
        /** @mago-expect analysis:possibly-undefined-int-array-index */
        $totals[$name]['visits'][$month] += 1.0;
    }

    foreach ($totals as $data) {
        for ($i = 1; $i <= 3; $i++) {
            /** @mago-expect analysis:possibly-undefined-int-array-index */
            echo $data['visits'][$i];
        }
    }

    return $totals;
}
