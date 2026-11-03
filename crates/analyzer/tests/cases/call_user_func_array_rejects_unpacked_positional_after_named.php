<?php

declare(strict_types=1);

namespace CallUserFuncArrayRejectsUnpackedPositionalAfterNamed;

/**
 * @param list<int> $list
 * @param array<string, int> $map
 * @param array<int> $values
 */
function run(array $list, array $map, array $values): void
{
    $rest = static fn (int ...$numbers): int => count($numbers);

    // @mago-expect analysis:positional-argument-after-named
    echo call_user_func_array($rest, ['n' => 1, ...$list]);
    // @mago-expect analysis:positional-argument-after-named
    echo call_user_func_array($rest, ['n' => 1, ...$values]);
    // @mago-expect analysis:positional-argument-after-named
    echo call_user_func_array($rest, [...$map, 1]);
    // @mago-expect analysis:positional-argument-after-named
    echo call_user_func_array($rest, [...$values, 1]);
    echo call_user_func_array($rest, ['n' => 1, ...$map]);
    echo call_user_func_array($rest, ['n' => 1, ...[]]);
    echo call_user_func_array($rest, [...$list, 1]);
    echo call_user_func_array($rest, [...$list, 'n' => 1]);
    echo call_user_func_array($rest, [...$map, 'n' => 1]);
}
