<?php

declare(strict_types=1);

namespace CallUserFuncArrayChecksArgumentsAgainstCallback;

final class Joiner
{
    public function join(int $number, string $text): string
    {
        return $number . $text;
    }
}

/**
 * @param list<string> $texts
 */
function run(Joiner $joiner, array $texts): void
{
    // @mago-expect analysis:invalid-argument
    call_user_func_array(static fn (int $number): int => $number, ['a']);
    call_user_func_array(static fn (int $number): int => $number, [1]);
    // @mago-expect analysis:invalid-argument
    call_user_func_array([$joiner, 'join'], [1, []]);
    call_user_func_array([$joiner, 'join'], [1, 'a']);
    // @mago-expect analysis:invalid-argument
    call_user_func_array(static fn (int $number): int => $number, [1 => 'a']);
    // @mago-expect analysis:invalid-argument
    call_user_func_array('strtoupper', array([]));
    call_user_func_array(static fn (int $number): int => $number, $texts);
    // @mago-expect analysis:invalid-argument
    call_user_func_array(static fn (int $number): int => $number, ['number' => 'a']);
    // @mago-expect analysis:invalid-argument
    call_user_func_array(static fn (int $number): int => $number, [...$texts]);
}
