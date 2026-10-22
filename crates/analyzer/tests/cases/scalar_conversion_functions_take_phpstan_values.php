<?php

declare(strict_types=1);

namespace ScalarConversionFunctionsTakePhpstanValues;

final class Label implements \Stringable
{
    public function __toString(): string
    {
        return 'label';
    }
}

function convert(mixed $value, string $text, ?int $maybe, Label $label, \stdClass $object): string
{
    // @mago-expect analysis:mixed-argument
    $a = intval($value);
    $b = intval($text) + intval($maybe) + intval([]);
    // @mago-expect analysis:invalid-argument
    $c = intval($object);
    // @mago-expect analysis:mixed-argument
    $d = floatval($value);
    // @mago-expect analysis:mixed-argument
    $e = strval($value);

    return $a . $b . $c . $d . $e . strval($label) . strval($maybe) . strval(1.5);
}
