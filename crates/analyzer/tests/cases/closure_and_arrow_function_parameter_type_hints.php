<?php

namespace Fixture;

// With only `check_closure_and_arrow_function_parameter_type_hints` enabled,
// untyped closure and arrow function parameters are reported even when their
// type is inferable, while return types, `$_` parameters and named functions
// stay unchecked.
function run(): void
{
    // @mago-expect analysis:missing-parameter-type
    $values = array_map(fn($value) => $value * 2, [1, 2, 3]);

    // @mago-expect analysis:missing-parameter-type
    $sorted = array_filter($values, function ($value) {
        return $value > 2;
    });

    $ignored = array_map(fn(int $value, $_index) => $value, $sorted, array_keys($sorted));

    echo count($ignored);
}

function untyped($value)
{
    return $value;
}
