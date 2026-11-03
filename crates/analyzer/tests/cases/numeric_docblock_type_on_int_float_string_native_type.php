<?php

declare(strict_types=1);

namespace NumericDocblockTypeOnIntFloatStringNativeType;

/**
 * @param numeric $value
 */
function numeric_parameter(int|float|string $value): string
{
    return (string) $value;
}

/**
 * @return numeric
 */
function numeric_return(int|float|string $value): int|float|string
{
    // @mago-expect analysis:invalid-return-statement
    return $value;
}

/**
 * @param numeric $value
 */
function numeric_argument(int|float|string $value): string
{
    return numeric_parameter($value);
}

/**
 * @param numeric $value
 */
function numeric_on_int_or_float(int|float $value): void // @mago-expect analysis:docblock-type-mismatch
{
    echo $value;
}
