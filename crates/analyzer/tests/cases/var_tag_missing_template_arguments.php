<?php

declare(strict_types=1);

/**
 * @template T
 */
interface Box
{
    /**
     * @return T
     */
    public function get(): mixed;
}

/**
 * @template T = mixed
 */
interface DefaultedBox
{
    /**
     * @return T
     */
    public function get(): mixed;
}

function source(): mixed
{
    return null;
}

function var_tags(): int
{
    /** @var Box $bare */ // @mago-expect analysis:missing-template-parameter
    $bare = source();
    /** @var list<Box>|null $nested */ // @mago-expect analysis:missing-template-parameter
    $nested = source();
    /** @var Box<int> $precise */
    $precise = source();
    /** @var DefaultedBox $defaulted */
    $defaulted = source();

    return count([$bare, $nested, $precise, $defaulted]);
}
