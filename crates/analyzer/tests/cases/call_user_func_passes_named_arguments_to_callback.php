<?php

declare(strict_types=1);

namespace CallUserFuncPassesNamedArgumentsToCallback;

final class Joiner
{
    public function join(int $number, string $text): string
    {
        return $number . $text;
    }
}

function rest(int ...$numbers): int
{
    return count($numbers);
}

/**
 * @no-named-arguments
 */
function positional(int $number): int
{
    return $number;
}

function run(Joiner $joiner): void
{
    // @mago-expect analysis:invalid-argument
    call_user_func_array(static fn (int $number): int => $number, ['number' => 'a']);
    call_user_func_array(static fn (int $number): int => $number, ['number' => 1]);
    // @mago-expect analysis:invalid-named-argument
    // @mago-expect analysis:too-few-arguments
    call_user_func_array(static fn (int $number): int => $number, ['other' => 1]);
    // @mago-expect analysis:invalid-named-argument
    call_user_func_array(static fn (int $number = 0): int => $number, ['other' => 1]);
    // @mago-expect analysis:too-few-arguments
    call_user_func_array(static fn (int $number, int $other = 0): int => $number + $other, ['other' => 1]);
    // @mago-expect analysis:invalid-argument
    call_user_func(static fn (int $number, int $other): int => $number + $other, 1, other: 'a');
    call_user_func(static fn (int $number, int $other): int => $number + $other, other: 1, number: 2);
    call_user_func_array([$joiner, 'join'], ['text' => 'a', 'number' => 1]);
    // @mago-expect analysis:invalid-argument
    call_user_func_array([$joiner, 'join'], [1, 'text' => 2]);
    // @mago-expect analysis:invalid-argument
    call_user_func_array('CallUserFuncPassesNamedArgumentsToCallback\rest', ['key' => 'a']);
    call_user_func_array('CallUserFuncPassesNamedArgumentsToCallback\rest', ['key' => 1]);
    // @mago-expect analysis:invalid-argument
    call_user_func_array('strtoupper', ['string' => []]);
    // @mago-expect analysis:invalid-named-argument
    // @mago-expect analysis:too-few-arguments
    call_user_func_array('strtoupper', ['text' => 'a']);
    // @mago-expect analysis:invalid-argument
    call_user_func_array(static fn (int $number): int => $number, ['0' => 'a']);
    call_user_func_array(static fn (int $number): int => $number, ['0' => 1]);
    // @mago-expect analysis:named-argument-not-allowed
    call_user_func('CallUserFuncPassesNamedArgumentsToCallback\positional', number: 1);
    // @mago-expect analysis:named-argument-not-allowed
    call_user_func_array('CallUserFuncPassesNamedArgumentsToCallback\positional', ['number' => 1]);
    call_user_func_array('CallUserFuncPassesNamedArgumentsToCallback\positional', [1]);
}
