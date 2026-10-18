<?php

declare(strict_types=1);

namespace Foo;

interface Named
{
    public function getName(): string;
}

class Box implements Named
{
    public function getName(): string
    {
        return 'box';
    }

    public function getFullName(): string
    {
        return 'full';
    }

    public static function tryFromName(string $name): ?self
    {
        return $name === '' ? null : new self();
    }

    public function doThing(): void
    {
    }
}

final class Kid extends Box
{
    // @mago-expect analysis:incorrect-method-casing
    public function dothing(): void
    {
        // @mago-expect analysis:incorrect-method-casing
        parent::DoThing();
    }

    public function getFullName(): string
    {
        return parent::getFullName();
    }
}

final class Label implements Named
{
    // @mago-expect analysis:incorrect-method-casing
    public function getname(): string
    {
        return 'label';
    }
}

/**
 * @method string whatever()
 */
final class Magic
{
    public function __call(string $name, array $arguments): string
    {
        return $name;
    }
}

function calls(Box $box, ?Box $maybe, Magic $magic): void
{
    echo $box->getFullName();
    // @mago-expect analysis:incorrect-method-casing
    echo $box->getFullname();
    // @mago-expect analysis:incorrect-method-casing
    echo $maybe?->GETFULLNAME();
    // @mago-expect analysis:incorrect-method-casing
    echo Box::tryfromname('x')?->getName();
    echo Box::tryFromName('x')?->getName();
    echo $magic->whatever();
    // @mago-expect analysis:incorrect-method-casing
    echo $magic->Whatever();
    // @mago-expect analysis:non-documented-method
    echo $magic->undeclared();
    // @mago-expect analysis:incorrect-method-casing
    echo (new \DateTimeImmutable())->FORMAT('Y');
}
