<?php

declare(strict_types=1);

namespace ArrayKeyExistsVariableKeyOnMixed;

function other_key_after_early_return(mixed $values, string $key): mixed
{
    if (!array_key_exists($key, $values)) { // @mago-expect analysis:mixed-argument
        return null;
    }

    return $values['x']; // @mago-expect analysis:possibly-undefined-string-array-index
}

function other_key_in_true_branch(mixed $values, int $key): mixed
{
    if (array_key_exists($key, $values)) { // @mago-expect analysis:mixed-argument
        return $values['x']; // @mago-expect analysis:possibly-undefined-string-array-index
    }

    return null;
}

function checked_key(mixed $values, string $key): mixed
{
    if (!array_key_exists($key, $values)) { // @mago-expect analysis:mixed-argument
        return null;
    }

    return $values[$key];
}
