<?php

declare(strict_types=1);

namespace Mago\Sdk\Internal\Syntax;

use Mago\Sdk\Exception\InvalidArgumentException;
use Mago\Sdk\Span;
use Mago\Sdk\Syntax\Node;
use Mago\Sdk\Syntax\NodeKind;

use function array_slice;
use function count;
use function ord;
use function unpack;

/**
 * Lazily materializes public nodes from Mago's fixed-width node table.
 *
 * @internal
 * @mago-expect lint:cyclomatic-complexity
 * @mago-expect lint:kan-defect
 * @mago-expect lint:too-many-methods
 */
final class NodeStore
{
    public const RECORD_SIZE = 21;

    private const NO_NODE = 4_294_967_295;

    private const PARENT_OFFSET = 9;

    private const FIRST_CHILD_OFFSET = 13;

    private const NEXT_SIBLING_OFFSET = 17;

    /**
     * @var array<int<0, max>, Node>
     */
    private array $nodes = [];

    /**
     * @var null|array<string, list<int<0, 4294967295>>>
     */
    private ?array $idsByKind = null;

    /**
     * @var array<string, list<Node>>
     */
    private array $nodesByKind = [];

    /**
     * @var array<int, int>
     */
    private array $lastIdsOfRoots = [];

    /**
     * @param list<NodeKind> $kinds
     * @param int<0, 4294967295> $nodeCount
     */
    public function __construct(
        private readonly array $kinds,
        private readonly string $records,
        private readonly int $nodeCount,
    ) {}

    public function get(int $id): Node
    {
        if ($id < 0 || $id >= $this->nodeCount) {
            throw new InvalidArgumentException("The source snapshot has no node with identifier {$id}.");
        }

        return $this->nodes[$id] ??= $this->materialize($id);
    }

    /**
     * @param array<int, int<0, 4294967295>> $ids
     *
     * @return list<Node>
     */
    public function getMany(array $ids): array
    {
        $nodes = [];
        foreach ($ids as $id) {
            $node = $this->nodes[$id] ?? null;
            if ($node === null) {
                $offset = $id * self::RECORD_SIZE;
                /** @var array{1: int<0, 4294967295>, 2: int<0, 4294967295>, 3: int<0, 4294967295>} $record */
                $record = unpack('N3', $this->records, $offset + 1);
                $parent = $record[3];
                $node = new Node(
                    $id,
                    $this->kinds[ord($this->records[$offset])],
                    new Span($record[1], $record[2]),
                    $parent === self::NO_NODE ? null : $parent,
                );
                $this->nodes[$id] = $node;
            }

            $nodes[] = $node;
        }

        return $nodes;
    }

    /**
     * @return list<Node>
     * @mago-expect lint:no-multi-assignments
     */
    public function getAll(?NodeKind $kind = null): array
    {
        if ($kind !== null) {
            return $this->nodesByKind[$kind->value] ??= $this->getMany($this->getIdsOfKind($kind));
        }

        $nodes = [];
        for ($id = 0; $id < $this->nodeCount; ++$id) {
            $nodes[] = $this->nodes[$id] ??= $this->materialize($id);
        }

        return $nodes;
    }

    /**
     * @return list<Node>
     * @mago-expect lint:no-multi-assignments
     */
    public function getChildren(Node $node): array
    {
        /** @var array{1: int<0, 4294967295>} $decoded */
        $decoded = unpack('N', $this->records, ($node->id * self::RECORD_SIZE) + 13);
        $children = [];
        $child = $decoded[1];
        while ($child !== self::NO_NODE) {
            /** @var array{1: int<0, 4294967295>, 2: int<0, 4294967295>, 3: int<0, 4294967295>} $childRecord */
            $childRecord = unpack('N3', $this->records, ($child * self::RECORD_SIZE) + 9);
            $children[] = $this->nodes[$child] ??= $this->materialize($child);
            $child = $childRecord[3];
        }

        return $children;
    }

    /**
     * @return list<Node>
     */
    public function getDescendants(Node $node, NodeKind $kind): array
    {
        $ids = $this->getIdsOfKind($kind);
        $offset = self::positionAfter($ids, $node->id);
        $length = self::positionAfter($ids, $this->getLastIdInSubtree($node->id)) - $offset;

        return $this->getMany(array_slice($ids, $offset, $length));
    }

    public function getFirstDescendant(Node $node, NodeKind $kind): ?Node
    {
        $ids = $this->getIdsOfKind($kind);
        $id = $ids[self::positionAfter($ids, $node->id)] ?? null;
        if ($id === null || $id > $this->getLastIdInSubtree($node->id)) {
            return null;
        }

        return $this->get($id);
    }

    /**
     * @return list<int<0, 4294967295>>
     */
    private function getIdsOfKind(NodeKind $kind): array
    {
        if ($this->idsByKind === null) {
            $idsByKind = [];
            for ($id = 0, $offset = 0; $id < $this->nodeCount; ++$id, $offset += self::RECORD_SIZE) {
                $idsByKind[$this->kinds[ord($this->records[$offset])]->value][] = $id;
            }

            /** @var array<string, list<int<0, 4294967295>>> $idsByKind */
            $this->idsByKind = $idsByKind;
        }

        return $this->idsByKind[$kind->value] ?? [];
    }

    /**
     * @param list<int<0, 4294967295>> $ids
     *
     * @return int<0, max>
     */
    private static function positionAfter(array $ids, int $id): int
    {
        $low = 0;
        $high = count($ids);
        while ($low < $high) {
            $middle = ($low + $high) >> 1;
            if ($ids[$middle] <= $id) {
                $low = $middle + 1;
                continue;
            }

            $high = $middle;
        }

        return $low;
    }

    private function getLastIdInSubtree(int $id): int
    {
        while (($sibling = $this->getLink($id, self::NEXT_SIBLING_OFFSET)) === self::NO_NODE) {
            $parent = $this->getLink($id, self::PARENT_OFFSET);
            if ($parent === self::NO_NODE) {
                return $this->lastIdsOfRoots[$id] ??= $this->getLastLeaf($id);
            }

            $id = $parent;
        }

        return $sibling - 1;
    }

    private function getLastLeaf(int $id): int
    {
        while (($child = $this->getLink($id, self::FIRST_CHILD_OFFSET)) !== self::NO_NODE) {
            while (($sibling = $this->getLink($child, self::NEXT_SIBLING_OFFSET)) !== self::NO_NODE) {
                $child = $sibling;
            }

            $id = $child;
        }

        return $id;
    }

    /**
     * @return int<0, 4294967295>
     */
    private function getLink(int $id, int $linkOffset): int
    {
        /** @var array{1: int<0, 4294967295>} $decoded */
        $decoded = unpack('N', $this->records, ($id * self::RECORD_SIZE) + $linkOffset);

        return $decoded[1];
    }

    /**
     * @param non-negative-int $id
     */
    private function materialize(int $id): Node
    {
        $offset = $id * self::RECORD_SIZE;
        /** @var array{1: int<0, 4294967295>, 2: int<0, 4294967295>, 3: int<0, 4294967295>} $record */
        $record = unpack('N3', $this->records, $offset + 1);
        $kind = $this->kinds[ord($this->records[$offset])];
        $start = $record[1];
        $end = $record[2];
        $parent = $record[3];

        return new Node($id, $kind, new Span($start, $end), $parent === self::NO_NODE ? null : $parent);
    }
}
