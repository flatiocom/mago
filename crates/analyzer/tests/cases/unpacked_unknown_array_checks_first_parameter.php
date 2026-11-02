<?php

namespace Fixture;

function bounds(float $bottom, float $left, float $top, float $right): float
{
    return $bottom + $left + $top + $right;
}

// PHPStan checks the value type of an unpacked array of unknown size against
// the first parameter it fills, list or keyed.
/** @param list<mixed> $list */
function unpacked_list(array $list): float
{
    // @mago-expect analysis:mixed-argument
    return bounds(...$list);
}

function unpacked_array(array $values): float
{
    // @mago-expect analysis:mixed-argument
    return bounds(...$values);
}

/** @param list<float> $floats */
function unpacked_floats(array $floats): float
{
    return bounds(...$floats);
}
