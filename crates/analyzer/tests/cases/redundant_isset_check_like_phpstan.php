<?php

declare(strict_types=1);

/**
 * @property int $magic
 */
final class RedundantIssetBox
{
    public int $withDefault = 0;
    public int $noDefault;
    public ?int $nullable = null;
    /** @var DateTimeImmutable */
    private $untyped;

    public function __construct(
        public readonly int $promotedReadonly,
        public int $promotedMutable,
    ) {
        $this->noDefault = 1;
        $this->untyped = new DateTimeImmutable();
    }

    public function untypedIsSet(): bool
    {
        return isset($this->untyped); // @mago-expect analysis:redundant-isset-check
    }

    public function __get(string $name): mixed
    {
        return null;
    }
}

final class RedundantIssetProbe
{
    public function offset(): bool
    {
        $a = ['a' => 1];

        return isset($a['a']); // @mago-expect analysis:redundant-isset-check
    }

    public function nestedOffset(): bool
    {
        $a = ['x' => ['y' => 1]];

        return isset($a['x']['y']); // @mago-expect analysis:redundant-isset-check
    }

    public function property(RedundantIssetBox $b): bool
    {
        return isset($b->withDefault); // @mago-expect analysis:redundant-isset-check
    }

    public function promotedReadonly(RedundantIssetBox $b): bool
    {
        return isset($b->promotedReadonly); // @mago-expect analysis:redundant-isset-check
    }

    public function variable(int $x): bool
    {
        return isset($x); // @mago-expect analysis:redundant-isset-check
    }

    public function each(int $x, RedundantIssetBox $b): bool
    {
        return isset($x, $b->withDefault); // @mago-expect analysis:redundant-isset-check,redundant-isset-check
    }

    /**
     * @param array<string, int> $map
     * @param list<int> $list
     *
     * @return list<bool>
     */
    public function maybeOffset(array $map, array $list, int $i): array
    {
        return [isset($map['k']), isset($list[$i])];
    }

    public function nullableOffset(?int $n): bool
    {
        $a = ['a' => $n];

        return isset($a['a']);
    }

    public function stringOffset(string $s): bool
    {
        return isset($s[2]);
    }

    /**
     * @param ArrayAccess<string, int> $o
     */
    public function arrayAccessObject(ArrayAccess $o): bool
    {
        return isset($o['k']);
    }

    /**
     * @return list<bool>
     */
    public function maybeUninitialized(RedundantIssetBox $b): array
    {
        return [isset($b->noDefault), isset($b->promotedMutable), isset($b->nullable), isset($b->magic)];
    }

    public function maybeDefined(bool $c): bool
    {
        if ($c) {
            $x = 1;
        }

        return isset($x);
    }

    /**
     * @return list<bool>
     */
    public function nullableOrMixed(?int $n, mixed $m): array
    {
        return [isset($n), isset($m)];
    }

    public function session(): bool
    {
        return isset($_SESSION);
    }

    public function staticLocal(): bool
    {
        static $cache = [];

        return isset($cache);
    }
}
