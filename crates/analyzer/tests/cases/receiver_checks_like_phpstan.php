<?php

declare(strict_types=1);

final class ReceiverRow
{
    public int $id = 1;

    public ?string $name = null;

    public function label(): string
    {
        return 'row';
    }
}

final class ReceiverBag
{
    /** @var array<string, array<string, int>> */
    public array $items = [];
}

final class ReceiverHolder
{
    private ?ReceiverBag $bag = null;

    public function setBag(ReceiverBag $bag): void
    {
        $this->bag = $bag;
    }

    public function writeOffset(string $key): void
    {
        $this->bag->items[$key] = ['a' => 1]; // @mago-expect analysis:possibly-null-property-access
    }

    public function writeNestedOffset(string $key): void
    {
        $this->bag->items[$key]['a'] = 1; // @mago-expect analysis:possibly-null-property-access
    }
}

function receiver_dynamic_name_on_nullable(?ReceiverRow $row, string $column): mixed
{
    return $row->$column; // @mago-expect analysis:string-member-selector
}

function receiver_dynamic_name_read_twice(?ReceiverRow $row, string $column): bool
{
    return $row->$column !== $row->$column; // @mago-expect analysis:string-member-selector
}

function receiver_literal_name_on_nullable(?ReceiverRow $row): ?int
{
    return $row->id; // @mago-expect analysis:possibly-null-property-access
}
