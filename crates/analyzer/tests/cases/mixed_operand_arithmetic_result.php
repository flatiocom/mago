<?php

declare(strict_types=1);

function take_int(int $value): int
{
    return $value;
}

function take_float(float $value): float
{
    return $value;
}

function mixed_arithmetic(mixed $amount, float $rate): void
{
    $reward = -1 * $amount; // @mago-expect analysis:mixed-operand
    take_int($reward); // @mago-expect analysis:possibly-invalid-argument

    $scaled = $amount * $rate; // @mago-expect analysis:mixed-operand
    take_float($scaled);

    $rest = $amount % 2; // @mago-expect analysis:mixed-operand
    take_int($rest);
}
