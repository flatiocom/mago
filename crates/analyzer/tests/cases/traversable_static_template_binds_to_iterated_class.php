<?php

declare(strict_types=1);

/**
 * @implements IteratorAggregate<int, static>
 */
class Node implements IteratorAggregate
{
    /**
     * @return Generator<int, static, mixed, void>
     */
    public function getIterator(): Generator
    {
        yield $this;
    }
}

final class Leaf extends Node
{
}

function takes_leaf(Leaf $leaf): void
{
    echo $leaf::class;
}

function takes_node(Node $node): void
{
    echo $node::class;
}

function iterate_leaf(Leaf $leaf): void
{
    foreach ($leaf as $child) {
        takes_leaf($child);
    }
}

function iterate_node(Node $node): void
{
    foreach ($node as $child) {
        takes_node($child);
    }
}

function iterate_node_as_leaf(Node $node): void
{
    foreach ($node as $child) {
        // @mago-expect analysis:less-specific-argument
        takes_leaf($child);
    }
}
