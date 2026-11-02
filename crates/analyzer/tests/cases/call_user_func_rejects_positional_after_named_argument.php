<?php

declare(strict_types=1);

namespace CallUserFuncRejectsPositionalAfterNamedArgument;

final class Joiner
{
    public function join(int $number, string $text): string
    {
        return $number . $text;
    }
}

function run(Joiner $joiner): void
{
    // @mago-expect analysis:positional-argument-after-named,too-few-arguments
    call_user_func_array([$joiner, 'join'], ['text' => 'a', 0 => 1]);
    // @mago-expect analysis:positional-argument-after-named,too-few-arguments
    call_user_func_array([$joiner, 'join'], ['text' => 'a', 1]);
    // @mago-expect analysis:positional-argument-after-named
    call_user_func_array(static fn (int $number): int => $number, ['number' => 1, ...[2]]);
    call_user_func_array([$joiner, 'join'], [1, 'text' => 'a']);
    call_user_func_array([$joiner, 'join'], ['number' => 1, 'text' => 'a']);
}
