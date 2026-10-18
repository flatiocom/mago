<?php

declare(strict_types=1);

final class Box
{
    public function label(): null|string
    {
        return rand(0, 1) ? null : 'label';
    }

    public function inner(): null|Box
    {
        return rand(0, 1) ? null : new Box();
    }
}

function takes_null(null $value): void
{
    var_dump($value);
}

function null_on_the_right(null|Box $box): void
{
    if ($box?->label() === null) {
        takes_null($box?->label());
    }
}

function null_on_the_left(null|Box $box): void
{
    if (null === $box?->inner()?->label()) {
        takes_null($box?->inner()?->label());
    }
}
