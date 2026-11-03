<?php

declare(strict_types=1);

namespace InterpolatedBracedArrayNameReadsTheVariable;

/**
 * @param array{k: string} $name
 */
function shape_element(array $name): string
{
    return "a ${name['k']}";
}

/**
 * @param list<string> $names
 */
function list_element(array $names, int $index): string
{
    return "a ${names[$index]}";
}

/**
 * @param array{k: string} $name
 */
function missing_element(array $name): string
{
    // @mago-expect analysis:undefined-string-array-index
    return "a ${name['x']}";
}

function braced_name(string $name): string
{
    return "a ${name}";
}
