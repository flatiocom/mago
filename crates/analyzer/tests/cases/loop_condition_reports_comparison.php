<?php

declare(strict_types=1);

namespace LoopConditionReportsComparison;

function while_that_breaks(int $value): int
{
    while ($value !== null) { // @mago-expect analysis:redundant-comparison
        echo $value;
        break;
    }

    return $value;
}

function for_that_breaks(int $value): int
{
    for (; null !== $value; ) { // @mago-expect analysis:redundant-comparison
        echo $value;
        break;
    }

    return $value;
}

function while_that_loops(int $value): int
{
    while ($value !== null) { // @mago-expect analysis:redundant-comparison
        $value++;
        if ($value > 5) {
            return $value;
        }
    }

    return 0;
}

function assigned_in_loop(): int
{
    $value = null;
    while ($value === null) {
        $value = 1;
    }

    return $value;
}

function counter(): int
{
    $i = 0;
    while ($i !== 10) {
        $i++;
    }

    return $i;
}

function bounded_counter(): int
{
    for ($i = 0; $i < 10; $i++) {
        if ($i === 9) {
            break;
        }
    }

    return 0;
}

/**
 * @param list<int> $values
 */
function shifted(array $values): void
{
    $value = array_shift($values);
    while ($value !== null) {
        $value = array_shift($values);
    }
}
