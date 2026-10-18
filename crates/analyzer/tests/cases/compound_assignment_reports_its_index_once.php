<?php

declare(strict_types=1);

function string_offset(string $text, mixed $offset): void
{
    // @mago-expect analysis:mixed-array-index
    $text[$offset] .= 'x';

    echo $text;
}

/**
 * @param array<string, string> $titles
 */
function concat_key(array $titles, int|float $key): void
{
    // @mago-expect analysis:mismatched-array-index
    $titles[$key] .= 'x';
}

/**
 * @param array<string, string> $titles
 */
function coalesce_key(array $titles, int|float $key): void
{
    // @mago-expect analysis:mismatched-array-index
    $titles[$key] ??= 'x';
}

/**
 * @param array<string, array<string, string>> $rows
 */
function nested_keys(array $rows, int|float $row, int|float $cell): void
{
    // @mago-expect analysis:mismatched-array-index
    // @mago-expect analysis:mismatched-array-index
    $rows[$row][$cell] .= 'x';
}

/**
 * @param array<string, string> $titles
 */
function known_path_key(array $titles, int|float $key): void
{
    // @mago-expect analysis:mismatched-array-index
    $titles[$key] = 'a';
    // @mago-expect analysis:mismatched-array-index
    $titles[$key] .= 'b';
}

/**
 * @param list<array{tax: int|float, base: float}> $items
 * @param array<string, array{base: float}> $vat
 */
function total_per_key_in_a_loop(array $items, array $vat): void
{
    foreach ($items as $item) {
        $tax = $item['tax'];
        // @mago-expect analysis:mismatched-array-index
        if (!isset($vat[$tax])) {
            // @mago-expect analysis:mismatched-array-index
            $vat[$tax] = ['base' => 0.0];
        }

        // @mago-expect analysis:mismatched-array-index
        $vat[$tax]['base'] += $item['base'];
    }
}

/**
 * @param array<string, int> $counts
 */
function increment_key(array $counts, int|float $key): void
{
    // @mago-expect analysis:mismatched-array-index
    $counts[$key]++;
}

/**
 * @param array<string, int> $counts
 */
function decrement_key(array $counts, int|float $key): void
{
    // @mago-expect analysis:mismatched-array-index
    --$counts[$key];
}

/**
 * @param list<string> $tags
 */
function count_per_tag(array $tags, bool $passed): void
{
    $counts = [];
    foreach ($tags as $tag) {
        if (!isset($counts[$tag])) {
            $counts[$tag] = [true => 0, false => 0];
        }

        // @mago-expect analysis:invalid-array-index
        $counts[$tag][$passed]++;
    }
}
