<?php

declare(strict_types=1);

namespace CallUserFuncSkipsUnpackedValueWithoutElementType;

use ArrayIterator;
use Countable;
use Traversable;

/**
 * @param Countable&Traversable<int, int> $counted
 * @param ArrayIterator<int, string> $texts
 */
function run(Countable&Traversable $counted, ArrayIterator $texts): void
{
    call_user_func_array(static fn (int $number): int => $number, [...$counted]);
    // @mago-expect analysis:invalid-argument
    call_user_func_array(static fn (int $number): int => $number, [...$texts]);
}
