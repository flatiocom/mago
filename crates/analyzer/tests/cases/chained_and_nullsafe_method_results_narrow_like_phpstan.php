<?php

declare(strict_types=1);

final class Controller
{
    public function getName(): null|string
    {
        return null;
    }
}

final class Control
{
    public function __construct(
        private readonly Controller $controller,
    ) {}

    public function getController(): Controller
    {
        return $this->controller;
    }

    public function controllerName(): string
    {
        if ($this->getController()->getName() === null) {
            return '';
        }

        return $this->getController()->getName();
    }
}

function nullsafe_name(null|Controller $controller): string
{
    if ($controller?->getName() === null) {
        return '';
    }

    return $controller->getName();
}
