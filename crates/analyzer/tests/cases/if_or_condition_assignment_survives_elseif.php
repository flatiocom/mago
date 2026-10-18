<?php

declare(strict_types=1);

/** @param list<array{code: string, to: DateTime}> $periods */
function last_before(array $periods, DateTime $date): null|string
{
    $last = null;
    $codes = [];

    foreach ($periods as $period) {
        if ($period['to'] >= $date) {
            continue;
        }

        if ($last === null || $period['to'] > $last) {
            $last = $period['to'];
            $codes = [$period['code'] => $period['code']];
        } elseif ($period['to']->format('Y-m-d') === $last->format('Y-m-d')) {
            $codes[$period['code']] = $period['code'];
        }
    }

    return count($codes) === 1 ? reset($codes) : null;
}

function assigned_in_or_branch(null|int $last, int $next, bool $newer, bool $other): int
{
    if ($last === null || $newer) {
        $last = $next;
    } elseif ($other) {
        echo $last;
    }

    return $last;
}

function kept_in_or_branch(null|int $last, bool $newer, bool $other): int
{
    if ($last === null || $newer) {
        echo 1;
    } elseif ($other) {
        echo $last;
    }

    /** @mago-expect analysis:nullable-return-statement,invalid-return-statement */
    return $last;
}
