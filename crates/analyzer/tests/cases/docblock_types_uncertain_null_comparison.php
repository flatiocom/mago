<?php

declare(strict_types=1);

final class UncertainNullStep
{
    public function step(): bool
    {
        return true;
    }
}

/**
 * @param string $value
 */
function uncertain_docblock_value_compared_with_null($value, UncertainNullStep $step): void
{
    while ($value !== null) {
        $step->step();
    }
}

function uncertain_native_value_compared_with_null(string $value, UncertainNullStep $step): void
{
    while ($value !== null) { // @mago-expect analysis:impossible-null-type-comparison
        $step->step();
    }
}
