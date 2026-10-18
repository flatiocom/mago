<?php

declare(strict_types=1);

final class ReadInInitBox
{
    public int $value = 0;
}

abstract class ReadInInitBase
{
    protected function startup(): void {}

    protected function request(): int
    {
        return 1;
    }
}

final class ReadInInitNoConstructor extends ReadInInitBase
{
    private ReadInInitBox $box;

    protected function startup(): void
    {
        parent::startup();
        echo $this->request();
        // @mago-expect analysis:uninitialized-property
        echo $this->box->value;
        $this->box = new ReadInInitBox();
        echo $this->box->value;
    }
}

final class ReadInInitWithConstructor extends ReadInInitBase
{
    private ReadInInitBox $assigned;
    private ReadInInitBox $late;

    public function __construct()
    {
        $this->assigned = new ReadInInitBox();
    }

    protected function startup(): void
    {
        echo $this->assigned->value;
        // @mago-expect analysis:uninitialized-property
        echo $this->late->value;
        $this->late = new ReadInInitBox();
    }

    public function render(): int
    {
        return $this->late->value + $this->assigned->value;
    }
}
