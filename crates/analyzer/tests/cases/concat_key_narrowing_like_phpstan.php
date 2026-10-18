<?php

declare(strict_types=1);

/**
 * @param array<array-key, mixed> $link
 */
function concat_key_single_literal_guarded(array $link): string
{
    $side = 'a';

    return is_string($link[$side . '@ref'] ?? null) ? $link[$side . '@ref'] : '';
}

/**
 * @param array<string, string> $map
 */
function concat_key_coalesce_guarded(array $map, string $side): string
{
    if (($map[$side . '@ref'] ?? null) !== null) {
        return $map[$side . '@ref'];
    }

    return '';
}

/**
 * @param array<string, string> $map
 */
function concat_key_two_variables_guarded(array $map, string $a, string $b): string
{
    if (isset($map[$a . $b])) {
        return $map[$a . $b];
    }

    return '';
}

/**
 * @param array<string, string> $map
 */
function concat_key_nested_guarded(array $map, string $a): string
{
    if (isset($map[$a . '-' . $a . '!'])) {
        return $map[$a . '-' . $a . '!'];
    }

    return '';
}

/**
 * @param array<string, string> $map
 */
function concat_key_written_then_read(array $map, string $side): string
{
    $map[$side . '@ref'] = 'v';

    return $map[$side . '@ref'];
}

/**
 * @param array<string, string> $map
 */
function concat_key_reassigned_after_isset(array $map, string $side): ?string
{
    if (isset($map[$side . '@ref'])) {
        $side = 'x' . $side;

        return $map[$side . '@ref']; // @mago-expect analysis:possibly-undefined-string-array-index
    }

    return null;
}

/**
 * @param array<string, string> $map
 */
function concat_key_compound_assigned_after_isset(array $map, string $side): ?string
{
    if (isset($map['r' . $side])) {
        $side .= 'x';

        return $map['r' . $side]; // @mago-expect analysis:possibly-undefined-string-array-index
    }

    return null;
}

/**
 * @param array<string, string> $map
 */
function concat_key_rhs_reassigned_after_isset(array $map, string $a, string $b): ?string
{
    if (isset($map[$a . $b])) {
        $b = 'z';

        return $map[$a . $b]; // @mago-expect analysis:possibly-undefined-string-array-index
    }

    return null;
}

/**
 * @param array<string, string> $map
 */
function concat_key_reassigned_after_write(array $map, string $side): void
{
    $map[$side . '@ref'] = 'v';
    $side = 'q';

    echo $map[$side . '@ref']; // @mago-expect analysis:possibly-undefined-string-array-index
}
