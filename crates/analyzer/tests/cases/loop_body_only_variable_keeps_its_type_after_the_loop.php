<?php

declare(strict_types=1);

function takes_int(int $value): void
{
    echo $value;
}

function takes_int_or_string(int|string $value): void
{
    echo $value;
}

/**
 * @param list<int> $rows
 */
function after_foreach(array $rows): string
{
    foreach ($rows as $row) {
        $s = 'a' . $row;
    }

    if (isset($s)) {
        // @mago-expect analysis:invalid-argument
        takes_int($s);
    }

    // @mago-expect analysis:possibly-undefined-variable
    return "({$s})";
}

/**
 * @param list<int> $rows
 */
function concat_under_isset(array $rows): string
{
    foreach ($rows as $row) {
        $s = 'a' . $row;
    }

    return isset($s) ? 'x' . $s . "({$s})" : '';
}

function after_while(int $n): string
{
    while ($n > 0) {
        $s = 'a';
        $n--;
    }

    return isset($s) ? 'x' . $s : '';
}

function after_for(int $n): string
{
    for ($i = 0; $i < $n; $i++) {
        $s = 'a';
    }

    return isset($s) ? 'x' . $s : '';
}

/**
 * @param list<string> $rows
 */
function key_and_value(array $rows): void
{
    foreach ($rows as $k => $row) {
        echo $row;
    }

    if (isset($k, $row)) {
        takes_int($k);
        // @mago-expect analysis:invalid-argument
        takes_int($row);
    }
}

/**
 * @param list<int> $rows
 */
function with_break(array $rows): void
{
    foreach ($rows as $row) {
        $s = $row;
        if ($row > 5) {
            break;
        }
        $s = 'a';
    }

    if (isset($s)) {
        // @mago-expect analysis:possibly-invalid-argument
        takes_int($s);
    }
}

/**
 * @param list<int> $rows
 */
function with_continue(array $rows): void
{
    foreach ($rows as $row) {
        $s = $row;
        if ($row > 5) {
            continue;
        }
        $s = 'a';
    }

    if (isset($s)) {
        // @mago-expect analysis:possibly-invalid-argument
        takes_int($s);
    }
}

/**
 * @param list<int> $rows
 */
function with_break_and_continue(array $rows): void
{
    foreach ($rows as $row) {
        $s = $row;
        if ($row > 9) {
            break;
        }
        if ($row > 5) {
            $s = 1.5;
            continue;
        }
        $s = 'a';
    }

    if (isset($s)) {
        // @mago-expect analysis:possibly-invalid-argument
        takes_int_or_string($s);
    }
}

function control_if(bool $b): string
{
    if ($b) {
        $s = 'a';
    }

    return isset($s) ? "({$s})" : '';
}

/**
 * @param non-empty-list<int> $rows
 */
function non_empty_with_continue(array $rows): void
{
    foreach ($rows as $row) {
        $s = $row;
        if ($row > 5) {
            continue;
        }
        $s = 'a';
    }

    // @mago-expect analysis:possibly-invalid-argument
    takes_int($s);
}
