<?php

declare(strict_types=1);

final class ReadBeforeInitBox
{
    public int $value = 0;
}

trait ReadBeforeInitTrait
{
    private ReadBeforeInitBox $fromTrait;
}

abstract class ReadBeforeInitParent
{
    protected ReadBeforeInitBox $inherited;

    public function __construct()
    {
        $this->inherited = new ReadBeforeInitBox();
    }
}

final class ReadBeforeInitReads
{
    use ReadBeforeInitTrait;

    private ReadBeforeInitBox $box;
    private ?int $nullable;
    private int $counter;

    public function __construct(bool $flag)
    {
        // @mago-expect analysis:uninitialized-property
        echo $this->box->value;
        // @mago-expect analysis:uninitialized-property
        $this->box->value = 1;
        // @mago-expect analysis:uninitialized-property
        echo isset($this->box) ? 1 : 0;
        // @mago-expect analysis:uninitialized-property
        echo $this->nullable ?? 0;
        // @mago-expect analysis:uninitialized-property
        echo $this->fromTrait->value;
        // @mago-expect analysis:uninitialized-property
        $this->counter++;

        if ($flag) {
            $this->box = new ReadBeforeInitBox();
        }

        // @mago-expect analysis:uninitialized-property
        var_dump($this->box);

        $this->box = new ReadBeforeInitBox();
        $this->nullable = $flag ? 1 : null;
        $this->fromTrait = new ReadBeforeInitBox();
        $this->counter = 0;
    }
}

final class ReadBeforeInitNoReads extends ReadBeforeInitParent
{
    private ReadBeforeInitBox $box;
    private readonly ReadBeforeInitBox $frozen;
    private ReadBeforeInitBox $assignedByMethod;
    /** @var list<int> */
    private array $items;
    private int $withDefault = 0;

    public function __construct()
    {
        parent::__construct();
        echo $this->inherited->value;
        echo $this->withDefault;
        echo $this->frozen->value;
        $this->items[] = 1;
        $this->items = [];
        $this->box = new ReadBeforeInitBox();
        echo $this->box->value;
        $this->frozen = new ReadBeforeInitBox();
        $this->assign();
        echo $this->assignedByMethod->value;
    }

    public function read(): int
    {
        return $this->box->value + $this->frozen->value + count($this->items);
    }

    private function assign(): void
    {
        $this->assignedByMethod = new ReadBeforeInitBox();
    }
}

abstract class ReadBeforeInitService
{
    protected function request(): int
    {
        return 1;
    }
}

final class ReadBeforeInitAfterInheritedCall extends ReadBeforeInitService
{
    private ReadBeforeInitBox $box;

    public function __construct()
    {
        echo $this->request();
        // @mago-expect analysis:uninitialized-property
        echo $this->box->value;
        $this->box = new ReadBeforeInitBox();
    }
}
