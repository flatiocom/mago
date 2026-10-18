<?php

declare(strict_types=1);

/**
 * @return non-falsy-string
 */
function non_falsy_from_literal_part(string $country, string $name): string
{
    return "/{$country}/{$name}.html";
}

/**
 * @param non-falsy-string $name
 * @return non-falsy-string
 */
function non_falsy_from_non_falsy_part(string $name): string
{
    return "{$name}";
}

/**
 * @param non-empty-string $a
 * @param non-empty-string $b
 * @return non-falsy-string
 */
function non_falsy_from_two_non_empty_parts(string $a, string $b): string
{
    return "{$a}{$b}";
}

/**
 * @param non-empty-string $a
 * @return non-falsy-string
 */
function non_falsy_from_non_empty_part_and_zero(string $a): string
{
    return "{$a}0";
}

/**
 * @param non-empty-literal-string $a
 * @return non-falsy-string&literal-string
 */
function non_falsy_literal(string $a): string
{
    return "x{$a}";
}

function non_falsy_survives_concat(string $dir, string $country): string
{
    $path = $dir . "/{$country}/x.html";
    /** @mago-expect analysis:impossible-condition */
    if (!$path) {
        return '';
    }

    return $path;
}

function zero_literal_part_can_be_falsy(string $b): string
{
    $path = "0{$b}";
    if (!$path) {
        return 'zero';
    }

    return $path;
}

/**
 * @param non-empty-string $a
 */
function one_non_empty_part_can_be_falsy(string $a): string
{
    $path = "{$a}";
    if (!$path) {
        return 'zero';
    }

    return $path;
}
