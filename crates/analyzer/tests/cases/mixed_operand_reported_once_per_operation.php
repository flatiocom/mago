<?php

declare(strict_types=1);

/**
 * @param '28'|'29'|'30'|'31' $days
 */
function mixed_left_over_a_literal_union(mixed $amount, string $days): bool
{
    // @mago-expect analysis:mixed-operand
    return is_int($amount / $days);
}

function mixed_right_under_a_number_union(int|float $amount, mixed $days): bool
{
    // @mago-expect analysis:mixed-operand
    return is_int($amount / $days);
}
