<?php

declare(strict_types=1);

final class Handlers
{
    /** @var callable(): void */
    private $onChange;

    /** @var callable(int): string */
    private $linkFactory;

    /** @var resource */
    private $stream;

    /** @var Closure(int): string */
    // @mago-expect analysis:missing-property-type
    private $closure;

    /** @var string */
    // @mago-expect analysis:missing-property-type
    private $name;

    /**
     * @param callable(): void $onChange
     * @param callable(int): string $linkFactory
     * @param resource $stream
     * @param Closure(int): string $closure
     * @param callable(): void $onDelete
     */
    public function __construct(
        callable $onChange,
        callable $linkFactory,
        $stream,
        Closure $closure,
        string $name,
        private $onDelete,
    ) {
        $this->onChange = $onChange;
        $this->linkFactory = $linkFactory;
        $this->stream = $stream;
        $this->closure = $closure;
        $this->name = $name;
    }

    public function run(): string
    {
        ($this->onDelete)();
        ($this->onChange)();
        fclose($this->stream);

        return $this->name . ($this->linkFactory)(1) . ($this->closure)(2);
    }
}

/**
 * @param callable(): void $callback
 */
// @mago-expect analysis:missing-parameter-type
function call_back($callback): void
{
    $callback();
}

/**
 * @return resource
 */
function open_handle()
{
    $handle = fopen('php://memory', 'rb');
    if ($handle === false) {
        exit(1);
    }

    return $handle;
}

call_back(static function (): void {});
$handlers = new Handlers(
    static function (): void {},
    static fn(int $id): string => (string) $id,
    open_handle(),
    static fn(int $id): string => (string) $id,
    'name',
    static function (): void {},
);
echo $handlers->run();
