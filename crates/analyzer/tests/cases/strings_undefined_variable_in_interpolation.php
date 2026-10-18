<?php

declare(strict_types=1);

function probe(): string
{
    /** @mago-expect analysis:undefined-variable */
    /** @mago-expect analysis:mixed-operand */
    return "value: {$undefined}";
}
