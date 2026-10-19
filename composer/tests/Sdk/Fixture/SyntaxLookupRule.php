<?php

declare(strict_types=1);

namespace Mago\Tests\Sdk\Fixture;

use Mago\Sdk\Linter\LintContext;
use Mago\Sdk\Linter\Rule;
use Mago\Sdk\Linter\RuleDefinition;
use Mago\Sdk\Reporting\Issue;
use Mago\Sdk\Reporting\Level;
use Mago\Sdk\Syntax\Node;
use Mago\Sdk\Syntax\NodeKind;
use Mago\Sdk\Syntax\SourceFile;

use function array_filter;
use function array_map;
use function array_values;
use function implode;

/** @mago-expect lint:cyclomatic-complexity */
final class SyntaxLookupRule implements Rule
{
    /**
     * @param non-empty-string $code
     * @param non-empty-list<NodeKind> $targets
     */
    public function __construct(
        private readonly string $code,
        private readonly array $targets,
    ) {}

    public function getDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            code: $this->code,
            name: 'Syntax lookups',
            description: 'Compares kind lookups with a depth-first walk for the SDK integration test.',
            defaultLevel: Level::Help,
            defaultEnabled: true,
            targets: $this->targets,
        );
    }

    public function lint(LintContext $context): void
    {
        $file = $context->file;
        $kinds = [];
        foreach ($file->getNodes() as $node) {
            $kinds[$node->kind->value] = $node->kind;
        }

        $mismatches = [];
        foreach ($kinds as $kind) {
            if (self::ids($file->getNodes($kind)) === self::ids(self::ofKind($file->getNodes(), $kind))) {
                continue;
            }

            $mismatches[] = "{$kind->value} in the file";
        }

        $nodes = [$context->node, ...$file->getDescendants($context->node)];
        foreach ($nodes as $node) {
            $mismatches = [...$mismatches, ...self::compareDescendants($file, $node, $kinds)];
        }

        $context->report(Issue::new(
            $mismatches === [] ? 'Lookups match' : 'Lookups differ: ' . implode(', ', $mismatches),
            $context->node->span,
        ));
    }

    /**
     * @param array<string, NodeKind> $kinds
     * @return list<string>
     */
    private static function compareDescendants(SourceFile $file, Node $node, array $kinds): array
    {
        $walked = [];
        foreach ($file->getDescendants($node) as $descendant) {
            $walked[$descendant->kind->value][] = $descendant->id;
        }

        $mismatches = [];
        foreach ($kinds as $kind) {
            $expected = $walked[$kind->value] ?? [];
            $first = $file->getFirstDescendant($node, $kind);
            if (
                self::ids($file->getDescendants($node, $kind)) === $expected
                && $first?->id === ($expected[0] ?? null)
            ) {
                continue;
            }

            $mismatches[] = "{$kind->value} under node {$node->id}";
        }

        return $mismatches;
    }

    /**
     * @param list<Node> $nodes
     * @return list<Node>
     */
    private static function ofKind(array $nodes, NodeKind $kind): array
    {
        return array_values(array_filter($nodes, static fn(Node $node): bool => $node->kind === $kind));
    }

    /**
     * @param list<Node> $nodes
     * @return list<int>
     */
    private static function ids(array $nodes): array
    {
        return array_map(static fn(Node $node): int => $node->id, $nodes);
    }
}
