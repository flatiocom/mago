<?php

declare(strict_types=1);

namespace Fixture;

final class Box
{
}

final class Writes
{
    // @mago-expect analysis:unused-property-type
    private ?int $onlyNull = null;

    // @mago-expect analysis:unused-property-type
    private ?Box $neverNull;

    private ?int $both = null;

    // @mago-expect analysis:unused-property-type
    private int|string|null $neverString = null;

    // @mago-expect analysis:unused-property-type
    private ?float $onlyInt = null;

    // @mago-expect analysis:unused-property-type
    private ?array $appended = [];

    // @mago-expect analysis:unused-property-type
    private static ?int $counter = 0;

    private ?array $matches = null;

    private ?string $coalesced = null;

    private ?Box $fromClosure = null;

    /** @var int|null */
    private $untyped;

    /** @var 'a'|'b' */
    private string $literal = 'a';

    // @mago-expect analysis:uninitialized-property
    private ?int $neverWritten;

    public ?int $public = null;

    public function __construct(
        private ?int $promoted = 1,
    ) {
        $this->neverNull = new Box();
    }

    public function write(int $value, string $subject): void
    {
        $this->onlyNull = null;
        $this->both = $value;
        $this->neverString = $value;
        $this->onlyInt = $value;
        $this->appended[] = $value;
        self::$counter++;
        preg_match('/a/', $subject, $this->matches);
        $this->coalesced ??= $subject;
        $this->untyped = $value;
        (function (): void {
            $this->fromClosure = new Box();
        })();
    }

    public function read(): void
    {
        echo $this->onlyNull, $this->both, $this->neverString, $this->onlyInt, self::$counter;
        echo $this->coalesced, $this->untyped, $this->literal, $this->neverWritten, $this->promoted;
        var_dump($this->neverNull, $this->appended, $this->matches, $this->fromClosure);
    }
}

final class DynamicWrite
{
    private ?int $value = null;

    public function write(string $name): void
    {
        // @mago-expect analysis:string-member-selector
        $this->$name = 1;
        echo $this->value;
    }
}

final class OffsetUnset
{
    /** @var array<int, array<int, int>>|null */
    private ?array $nested;

    /** @var array<int, int>|null */
    private ?array $single;

    public function __construct()
    {
        $this->nested = [];
        $this->single = [];
    }

    public function remove(int $key): void
    {
        unset($this->nested[$key][$key], $this->single[$key]);
        var_dump($this->nested, $this->single);
    }
}

final class MixedWrite
{
    // @mago-expect analysis:unused-property-type
    private ?int $value = null;

    public function write(mixed $values): void
    {
        // @mago-expect analysis:mixed-property-access
        $values->value = 1;
        echo $this->value;
    }
}

final class NarrowedMixedWrite
{
    private ?int $value = null;

    public function write(mixed $values): void
    {
        if (!isset($values->tags)) {
            return;
        }

        // @mago-expect analysis:mixed-property-access
        $values->value = 1;
        echo $this->value;
    }
}

trait WritesHostValue
{
    public function useText(): void
    {
        $this->value = 'text';
    }
}

final class TraitWriteHost
{
    use WritesHostValue;

    private int|string $value = 1;

    public function read(): int|string
    {
        return $this->value;
    }
}
