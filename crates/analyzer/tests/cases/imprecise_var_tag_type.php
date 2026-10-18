<?php

declare(strict_types=1);

function source(): mixed
{
    return json_decode('[]', true);
}

function var_tags(): int
{
    /** @var array $bare */ // @mago-expect analysis:imprecise-type
    $bare = source();
    /** @var list $list */ // @mago-expect analysis:imprecise-type
    $list = source();
    /** @var array<string, array>|null $nested */ // @mago-expect analysis:imprecise-type
    $nested = source();
    /** @var array{a: iterable} $shape */ // @mago-expect analysis:imprecise-type
    $shape = source();
    /** @var array<string, mixed> $precise */
    $precise = source();

    return count($bare) + count($list) + count($nested ?? []) + count($shape) + count($precise);
}
