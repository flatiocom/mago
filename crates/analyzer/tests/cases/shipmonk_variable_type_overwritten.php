<?php

enum Status
{
    case Active;
    case Closed;
}

function int_to_string(): string
{
    $x = 1;
    echo $x;
    // @mago-expect analysis:shipmonk-variable-type-overwritten
    $x = 'a';

    return $x;
}

/**
 * @return list<string>
 */
function shape_to_other_shape(): array
{
    $x = ['a' => 1];
    echo $x['a'];
    // @mago-expect analysis:shipmonk-variable-type-overwritten
    $x = ['b'];

    return $x;
}

function int_to_float(int $value, float $ratio): float
{
    $x = $value;
    echo $x;
    // @mago-expect analysis:shipmonk-variable-type-overwritten
    $x = $value * $ratio;

    return $x;
}

function numeric_to_string(mixed $x): string
{
    if (is_numeric($x)) {
        $x = 'n';
        echo $x;
    }

    return 'done';
}

/**
 * @param list<list<mixed>> $rows
 * @param array<int, string> $row
 *
 * @return list<array<int, string>>
 */
function list_to_int_keyed_array(array $rows, array $row): array
{
    $x = $rows;
    echo count($x);
    $x = [$row];

    return $x;
}

function literal_to_literal(): int
{
    $x = 1;
    echo $x;
    $x = 2;

    return $x;
}

function narrowing(int|string $value): int
{
    $x = $value;
    echo $x;
    $x = 1;

    return $x;
}

function widening(int $value, int|string $wide): int|string
{
    $x = $value;
    echo $x;
    $x = $wide;

    return $x;
}

function null_initialized(): string
{
    $x = null;
    echo $x;
    $x = 'a';

    return $x;
}

function nullable_to_value(?int $value): int
{
    $x = $value;
    echo $x;
    $x = 1;

    return $x;
}

function mixed_to_value(mixed $x): string
{
    $x = 'a';

    return $x;
}

function enum_case_to_enum_case(): Status
{
    $x = Status::Active;
    echo $x->name;
    $x = Status::Closed;

    return $x;
}

/**
 * @return array<string, int>
 */
function shape_to_same_kind_shape(): array
{
    $x = ['a' => 1];
    echo $x['a'];
    $x = ['b' => 2, 'c' => 3];

    return $x;
}

function compound_assignment(int $value): string
{
    $x = $value;
    echo $x;
    $x .= 'a';

    return $x;
}

function destructuring(): string
{
    $x = 1;
    echo $x;
    [$x] = ['a'];

    return $x;
}

function possibly_undefined(bool $flag): string
{
    if ($flag) {
        $x = 1;
        echo $x;
    }

    $x = 'a';

    return $x;
}

/**
 * @param list<string> $items
 */
function foreach_loop(array $items): int|string
{
    $x = 1;
    echo $x;
    foreach ($items as $item) {
        $x = $item;
    }

    return $x;
}

function while_loop(): int|string
{
    $x = 1;
    echo $x;
    while (mt_rand(0, 1) === 1) {
        $x = 'a';
    }

    return $x;
}

function do_while_loop(): int|string
{
    $x = 1;
    echo $x;
    do {
        $x = 'a';
    } while (mt_rand(0, 1) === 1);

    return $x;
}
