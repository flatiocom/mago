<?php

declare(strict_types=1);

function castMixed(mixed $value): string
{
    // @mago-expect analysis:invalid-type-cast
    return (string) $value;
}

function binaryCastMixed(mixed $value): string
{
    // @mago-expect analysis:invalid-type-cast
    return (binary) $value;
}

/**
 * @template T
 *
 * @param T $value
 */
function castUnboundedTemplate($value): string
{
    // @mago-expect analysis:invalid-type-cast
    return (string) $value;
}

/**
 * @template T of mixed
 *
 * @param T $value
 */
function castMixedTemplate($value): string
{
    // @mago-expect analysis:invalid-type-cast
    return (string) $value;
}

function castNarrowedString(mixed $value): string
{
    if (is_string($value)) {
        return $value;
    }

    return '';
}

function castNarrowedScalar(mixed $value): string
{
    if (is_scalar($value)) {
        return (string) $value;
    }

    return '';
}

function interpolateMixed(mixed $value): string
{
    // @mago-expect analysis:mixed-operand
    return "value: {$value}";
}

interface Label
{
    public function __toString(): string;
}

/**
 * @template T
 *
 * @param T&Label $value
 */
function castTemplateIntersectedWithStringable($value): string
{
    return (string) $value;
}
