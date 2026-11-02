<?php

declare(strict_types=1);

namespace CallUserFuncFindsCallbackAndArgumentsByName;

function run(): void
{
    // @mago-expect analysis:invalid-argument
    call_user_func_array(callback: 'strtoupper', args: [[]]);
    // @mago-expect analysis:invalid-argument
    call_user_func_array(args: [[]], callback: 'strtoupper');
    // @mago-expect analysis:invalid-argument
    call_user_func_array('strtoupper', args: [[]]);
    call_user_func_array(callback: 'strtoupper', args: ['a']);
    // @mago-expect analysis:too-few-arguments
    call_user_func(callback: static fn (int $number): int => $number);
}
