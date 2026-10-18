<?php

declare(strict_types=1);

namespace Fixture;

final class Document
{
    public function add(string $name, mixed $value): self
    {
        return $this;
    }
}

final class Fragment
{
    public function add(string $name, mixed $value): self
    {
        return $this;
    }
}

/**
 * @param list<int> $row
 */
function append(Document|Fragment $node, mixed $name, mixed $amount, array $row, mixed $key): void
{
    // @mago-expect analysis:mixed-argument
    $node->add($name, 1);

    // @mago-expect analysis:mixed-operand
    $node->add('total', $amount + 1);

    // @mago-expect analysis:mixed-array-index
    $node->add('count', $row[$key]);
}

/**
 * @param array<string, list<int>> $groups
 */
function last(array $groups, mixed $key): void
{
    // @mago-expect analysis:mixed-array-index
    end($groups[$key]);
}
