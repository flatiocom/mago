<?php

declare(strict_types=1);

namespace DocblockVarCallablePair;

final class Sealed
{
}

function instance_method_through_class_string(): int
{
    // @mago-expect analysis:docblock-type-mismatch
    /** @var array{id: int}|callable $callback */
    $callback = ['DateTime', 'format'];

    return is_array($callback) ? 1 : 0;
}

function missing_method_of_final_class(): int
{
    // @mago-expect analysis:docblock-type-mismatch
    /** @var array{id: int}|callable $callback */
    $callback = [Sealed::class, 'nope'];

    return is_array($callback) ? 1 : 0;
}

function array_for_a_closure(): int
{
    // @mago-expect analysis:docblock-type-mismatch
    /** @var array{id: int}|\Closure $callback */
    $callback = ['DateTime', 'createFromFormat'];

    return is_array($callback) ? 1 : 0;
}

function static_method_through_class_string(): int
{
    /** @var array{id: int}|callable $callback */
    $callback = ['DateTime', 'createFromFormat'];

    return is_array($callback) ? 1 : 0;
}

function missing_method_of_open_class(): int
{
    /** @var array{id: int}|callable $callback */
    $callback = [\ArrayObject::class, 'nope'];

    return is_array($callback) ? 1 : 0;
}
