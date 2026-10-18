<?php

declare(strict_types=1);

final class First
{
    /** @var array<callable(string): void> */
    public array $onResult = [];

    public int $count = 0;
}

final class Second
{
    /** @var array<callable(int): void> */
    public array $onResult = [];

    public string $count = '';
}

function test_append_to_union_receiver(First|Second $control): void
{
    $control->onResult[] = function (): void {};
}

function test_direct_write_to_union_receiver(First|Second $control): void
{
    $control->count = 1; // @mago-expect analysis:invalid-property-assignment-value
}

function test_offset_write_outside_every_member(First|Second $control): void
{
    $control->onResult[] = 'not callable'; // @mago-expect analysis:invalid-property-assignment-value,invalid-property-assignment-value
}
