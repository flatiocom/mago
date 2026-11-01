<?php

declare(strict_types=1);

namespace InArrayAlwaysTrueLikePhpstan;

enum Single
{
    case Only;
}

enum Pair: string
{
    case Left = 'left';
    case Right = 'right';
}

function stop(): never
{
    exit(1);
}

/**
 * @mago-expect analysis:redundant-type-comparison
 */
function narrowed_by_switch(string $extension): void
{
    switch ($extension) {
        case 'pdf':
        case 'png':
            break;
        default:
            stop();
    }

    if (in_array($extension, ['pdf', 'jpg', 'png'], true)) {
        echo 1;
    }
}

/**
 * @param 'a'|'b' $value
 *
 * @mago-expect analysis:redundant-type-comparison(3)
 * @mago-expect analysis:redundant-condition
 */
function literal_union(string $value): void
{
    if (in_array($value, ['a', 'b', 'a'], true)) {
        echo 1;
    }

    if (!in_array($value, ['b', 'a'], true)) {
        echo 2;
    }

    $found = in_array(needle: $value, haystack: ['a', 'b'], strict: true);
    echo $found ? 3 : 4;
}

/**
 * @mago-expect analysis:redundant-type-comparison(2)
 */
function single_case_enum(Single $single): void
{
    if (in_array($single, [Single::Only], true)) {
        echo 1;
    }

    if (in_array($single, [Single::Only])) {
        echo 2;
    }
}

/**
 * @mago-expect analysis:redundant-type-comparison(4)
 */
function constant_needles(bool $flag): void
{
    if (in_array('a', ['a'], true)) {
        echo 1;
    }

    if (in_array(1, [1, '1'], true)) {
        echo 2;
    }

    if (in_array($flag, [true, false], true)) {
        echo 3;
    }

    if (in_array(null, [null], true)) {
        echo 4;
    }
}

/**
 * @param non-empty-list<'a'> $list
 * @param array{0: 'a', 1?: 'b'} $shape
 * @param array{'a', 'b'}|array{'a'} $shapes
 *
 * @mago-expect analysis:redundant-type-comparison(3)
 */
function typed_haystacks(array $list, array $shape, array $shapes): void
{
    if (in_array('a', $list, true)) {
        echo 1;
    }

    if (in_array('a', $shape, true)) {
        echo 2;
    }

    if (in_array('a', $shapes, true)) {
        echo 3;
    }
}

/**
 * @mago-expect analysis:redundant-type-comparison(2)
 */
function every_case_of_an_enum(Pair $pair, string $other): void
{
    if (in_array($pair, [Pair::Left, Pair::Right], true)) {
        echo 1;
    }

    $value = $pair->value;
    if (in_array($value, ['left', 'right', $other], true)) {
        echo 2;
    }
}

/**
 * @param 'a'|'b' $value
 * @param 'a'|'b'|'c' $wider
 * @param list<'a'|'b'> $list
 * @param array{0: 'a', 1?: 'b'} $shape
 * @param array{'a'|'b'} $loose
 *
 * @mago-expect analysis:impossible-type-comparison
 */
function possibly_false(string $value, string $wider, string $any, array $list, array $shape, array $loose, int $int, bool $strict): void
{
    if (in_array($wider, ['a', 'b'], true)) {
        echo 1;
    }

    if (in_array($any, ['a', 'b'], true)) {
        echo 2;
    }

    if (in_array($value, $list, true)) {
        echo 3;
    }

    if (in_array($value, $shape, true)) {
        echo 4;
    }

    if (in_array($value, $loose, true)) {
        echo 5;
    }

    if (in_array($int, [1, 2], true)) {
        echo 6;
    }

    if (in_array($value, ['a', 'b'])) {
        echo 7;
    }

    if (in_array($value, ['a', 'b'], $strict)) {
        echo 8;
    }

    if (in_array($value, [], true)) {
        echo 9;
    }

    if (in_array($value, ['b'], true)) {
        echo 10;
    }
}

/**
 * @param 'a'|'b' $value
 *
 * @throws \InvalidArgumentException
 *
 * @mago-expect analysis:redundant-type-comparison(4)
 * @mago-expect analysis:unreachable-else-clause(2)
 * @mago-expect analysis:unevaluated-code(4)
 */
function last_conditions(string $value, int $kind): int
{
    if ($kind === 1) {
        echo 1;
    } elseif (in_array($value, ['a', 'b'], true)) {
        echo 2;
    }

    if ($kind === 1) {
        echo 1;
    } elseif (in_array($value, ['a', 'b'], true)) {
        echo 2;
    } else {
        throw new \InvalidArgumentException('kind');
    }

    if ($kind === 1) {
        echo 1;
    } elseif (in_array($value, ['a', 'b'], true)) {
        echo 2;
    } else {
        echo 3;
    }

    if ($kind === 1) {
        echo 1;
    } elseif (in_array($value, ['a', 'b'], true)) {
        echo 2;
    } elseif ($kind === 3) {
        echo 3;
    }

    if ($kind === 1) {
        echo 1;
    } elseif (!in_array($value, ['a', 'b'], true)) {
        echo 2;
    }

    $closure =
        /** @throws \InvalidArgumentException */
        static function () use ($value): void {
            echo 0;
            if (in_array($value, ['a', 'b'], true)) {
                return;
            }

            throw new \InvalidArgumentException('value');
        };
    $closure();

    $result = match (true) {
        $kind === 1 => 1,
        in_array($value, ['a', 'b'], true) => 2,
    };

    if (in_array($value, ['a', 'b'], true)) {
        return $result;
    }

    throw new \InvalidArgumentException('value');
}
