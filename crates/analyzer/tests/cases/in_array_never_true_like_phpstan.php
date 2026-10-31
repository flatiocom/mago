<?php

declare(strict_types=1);

namespace InArrayNeverTrueLikePhpstan;

enum Suit
{
    case Hearts;
    case Spades;
}

/**
 * @param 'c'|'d' $other
 * @param list<'a'|'b'> $list
 * @param array<string, int> $map
 *
 * @mago-expect analysis:impossible-type-comparison(9)
 */
function never_true(string $string, int $int, string $other, array $list, array $map, Suit $suit): void
{
    if (in_array('c', ['a', 'b'], true)) {
        echo 1;
    }

    if (!in_array($string, [], true)) {
        echo 2;
    }

    if (in_array($int, ['a', 'b'], true)) {
        echo 3;
    }

    if (in_array($other, ['a', 'b'], true)) {
        echo 4;
    }

    if (in_array('c', $list, true)) {
        echo 5;
    }

    if (in_array($string, $map, true)) {
        echo 6;
    }

    if (in_array(null, ['a', 'b'], true)) {
        echo 7;
    }

    if (in_array($suit, [1, 2])) {
        echo 8;
    }

    if (in_array(1.0, [1, 2], true)) {
        echo 9;
    }
}

/**
 * @param non-empty-list<string> $strings
 * @param non-empty-list<'a'|'b'> $pair
 * @param non-empty-list<'a'> $single
 *
 * @mago-expect analysis:impossible-type-comparison
 */
function non_empty_typed_haystack(int $int, array $strings, array $pair, array $single): void
{
    if (in_array($int, $strings, true)) {
        echo 1;
    }

    if (in_array('c', $pair, true)) {
        echo 2;
    }

    if (in_array('b', $single, true)) {
        echo 3;
    }
}

/**
 * @param list<string> $list
 */
function possibly_true(string $string, ?int $nullable, array $list, bool $strict): void
{
    if (in_array($string, ['a', 'b'], true)) {
        echo 1;
    }

    if (in_array($nullable, ['a', null], true)) {
        echo 2;
    }

    if (in_array($string, $list, true)) {
        echo 3;
    }

    if (in_array('c', ['a', 'b'], $strict)) {
        echo 4;
    }

    if (in_array('c', ['a', 'b'])) {
        echo 5;
    }
}
