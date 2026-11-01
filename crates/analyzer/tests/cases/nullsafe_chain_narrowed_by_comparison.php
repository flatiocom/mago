<?php

declare(strict_types=1);

namespace NullsafeChainNarrowedByComparison;

final class Node
{
    public ?Node $parent = null;

    public ?string $name = null;
}

function chain(?Node $node): string
{
    if ($node?->parent?->name !== null) {
        // @mago-expect analysis:redundant-nullsafe-operator
        // @mago-expect analysis:redundant-nullsafe-operator
        return $node?->parent?->name;
    }

    return '';
}

function chain_read_without_nullsafe(?Node $node): string
{
    if ($node?->parent?->name !== null) {
        return $node->parent->name;
    }

    return '';
}

function chain_compared_identical_to_null(?Node $node): string
{
    if ($node?->parent?->name === null) {
        return '';
    }

    // @mago-expect analysis:redundant-nullsafe-operator
    // @mago-expect analysis:redundant-nullsafe-operator
    return $node?->parent?->name;
}

function chain_not_narrowed(?Node $node): ?string
{
    return $node?->parent?->name;
}
