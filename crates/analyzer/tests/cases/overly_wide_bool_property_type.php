<?php

declare(strict_types=1);

namespace Fixture;

trait SetsFlag
{
    public function raise(): void
    {
        // @mago-expect analysis:non-existent-property
        $this->flag = true;
    }
}

trait Greets
{
    public function greet(): string
    {
        return 'hi';
    }
}

abstract class Greeter
{
    use Greets;
}

final class Flags extends Greeter
{
    // @mago-expect analysis:overly-wide-bool-property-type
    private bool $neverTrue = false;

    // @mago-expect analysis:overly-wide-bool-property-type
    private bool $neverFalse = true;

    // @mago-expect analysis:overly-wide-bool-property-type
    private static bool $staticNeverTrue = false;

    // @mago-expect analysis:overly-wide-bool-property-type
    /** @var bool */
    private $docblockNeverTrue = false;

    private bool $both = false;

    private bool $fromArrow = false;

    private bool $fromStatic = false;

    private bool $fromLoop = false;

    private bool $fromParameter = false;

    private bool $fromReference = false;

    private ?bool $nullable = false;

    // @mago-expect analysis:overly-wide-bool-property-type
    private bool $onlyConstructor;

    public bool $public = false;

    public function __construct(
        private bool $promoted = false,
    ) {
        $this->onlyConstructor = false;
    }

    public function write(bool $value, int $count): void
    {
        $this->neverTrue = false;
        $this->both = true;
        $this->fromParameter = $value;
        $this->nullable = null;

        $raise = fn(): bool => $this->fromArrow = true;
        $raise();

        $next = false;
        while ($count-- > 0) {
            $this->fromLoop = $next;
            $next = true;
        }

        $this->setByReference($this->fromReference);
    }

    public static function raiseStatic(): void
    {
        self::$staticNeverTrue = false;
    }

    private function setByReference(bool &$target): void
    {
        $target = true;
    }

    public function read(): bool
    {
        return $this->neverTrue
            || $this->neverFalse
            || self::$staticNeverTrue
            || $this->docblockNeverTrue
            || $this->both
            || $this->fromArrow
            || $this->fromStatic
            || $this->fromLoop
            || $this->fromParameter
            || $this->fromReference
            || $this->nullable === true
            || $this->onlyConstructor
            || $this->promoted;
    }

    public static function raiseFromStatic(self $flags): void
    {
        $flags->fromStatic = true;
    }
}

final class UsesTrait
{
    use SetsFlag;

    private bool $flag = false;

    public function isFlagged(): bool
    {
        return $this->flag;
    }
}

// @mago-expect analysis:missing-constructor
final class NeverWritten
{
    private bool $flag;

    public function isFlagged(): bool
    {
        return $this->flag;
    }
}
