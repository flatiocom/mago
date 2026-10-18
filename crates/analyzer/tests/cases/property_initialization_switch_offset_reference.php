<?php

declare(strict_types=1);

final class SwitchInit
{
    private string $type;

    /** @throws InvalidArgumentException */
    public function __construct(string $kind)
    {
        switch ($kind) {
            case 'a':
                $this->type = 'A';
                break;
            case 'b':
                $this->type = 'B';
                break;
            default:
                throw new InvalidArgumentException($kind);
        }
    }

    public function type(): string
    {
        return $this->type;
    }
}

final class SwitchWithoutDefault
{
    // @mago-expect analysis:uninitialized-property
    private string $type;

    public function __construct(string $kind)
    {
        switch ($kind) {
            case 'a':
                $this->type = 'A';
                break;
        }
    }

    public function type(): string
    {
        return $this->type;
    }
}

final class SwitchCaseSkipsIt
{
    // @mago-expect analysis:uninitialized-property
    private string $type;

    public function __construct(string $kind)
    {
        switch ($kind) {
            case 'a':
                $this->type = 'A';
                break;
            default:
                break;
        }
    }

    public function type(): string
    {
        return $this->type;
    }
}

final class OffsetInit
{
    /** @var array<string, array<string, int>> */
    private array $prices;

    public function __construct()
    {
        $this->prices['base']['hour'] = 1;
    }

    /** @return array<string, array<string, int>> */
    public function prices(): array
    {
        return $this->prices;
    }
}

final class Document
{
    /** @var array<string, string> */
    public array $namespaces = [];
}

final class ReferenceInit
{
    /** @var array<string, string> */
    private array $namespaces;

    public function __construct(Document $document)
    {
        $this->namespaces = &$document->namespaces;
    }

    /** @return array<string, string> */
    public function namespaces(): array
    {
        return $this->namespaces;
    }
}

final class CompoundAssignment
{
    // @mago-expect analysis:uninitialized-property
    private string $label;

    public function __construct()
    {
        // @mago-expect analysis:uninitialized-property
        $this->label .= 'x';
    }

    public function label(): string
    {
        return $this->label;
    }
}
