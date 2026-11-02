<?php

declare(strict_types=1);

namespace CallUserFuncReturnsFunctionNameCallbackType;

final class Repeater
{
    public static function twice(string $text): string
    {
        return $text . $text;
    }
}

function upper(string $text): string
{
    return strtoupper($text);
}

function test_built_in_function_name(string $text): string
{
    return call_user_func('strtoupper', $text);
}

function test_user_function_name(string $text): string
{
    return call_user_func('CallUserFuncReturnsFunctionNameCallbackType\upper', $text);
}

function test_static_method_name(string $text): string
{
    return call_user_func('CallUserFuncReturnsFunctionNameCallbackType\Repeater::twice', $text);
}

function test_function_name_of_call_user_func_array(string $text): string
{
    return call_user_func_array('strtoupper', [$text]);
}

function test_return_type_mismatch(string $text): int
{
    // @mago-expect analysis:invalid-return-statement
    return call_user_func('strtoupper', $text);
}
