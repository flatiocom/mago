<?php

declare(strict_types=1);

final class StableMethodController
{
    public function getAction(): string
    {
        return 'default';
    }

    /**
     * @param 'all'|'archived' $action
     */
    public function redirectTo(string $action): void
    {
        echo $action;
    }

    public function startup(): void
    {
        if (in_array($this->getAction(), ['all', 'archived'], true)) {
            $this->redirectTo($this->getAction());
        }
    }

    public function afterEarlierCall(): void
    {
        $this->getAction();
        if (in_array($this->getAction(), ['all', 'archived'], true)) {
            $this->redirectTo($this->getAction());
        }
    }
}
