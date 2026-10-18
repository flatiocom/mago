<?php

declare(strict_types=1);

final class Item
{
    public string $title = 'item';
}

final class Other
{
}

final class DocblockChecks
{
    /** @var Item */
    private $item;

    /**
     * @param Item $item
     */
    public function __construct($item)
    {
        $this->item = $item;
    }

    /**
     * @param Item $item
     */
    public function falsy($item): string
    {
        if (!$item) {
            return 'none';
        }

        return $item->title;
    }

    /**
     * @param Item $item
     */
    public function instanceOfOther($item): bool
    {
        return $item instanceof Other;
    }

    /**
     * @param Item $item
     */
    public function variableIsset($item): bool
    {
        return isset($item);
    }

    public function propertyIsset(): bool
    {
        return isset($this->item); // @mago-expect analysis:redundant-isset-check
    }
}

final class NativeChecks
{
    private string $title = 'native';

    public function falsy(Item $item): string
    {
        if (!$item) { // @mago-expect analysis:impossible-condition
            return 'none';
        }

        return $item->title;
    }

    public function instanceOfOther(Item $item): bool
    {
        return $item instanceof Other; // @mago-expect analysis:impossible-type-comparison
    }

    public function variableIsset(Item $item): bool
    {
        return isset($item); // @mago-expect analysis:redundant-isset-check
    }

    public function propertyIsset(): bool
    {
        return isset($this->title); // @mago-expect analysis:redundant-isset-check
    }

    public function storedCheck(?Item $item, Closure $callback): bool
    {
        $wasNull = $item === null;
        $callback();

        return $wasNull && $item !== null;
    }

    public function timezone(DateTimeInterface $date): ?DateTimeZone
    {
        $timezone = $date->getTimezone();

        return $timezone === false ? null : $timezone;
    }
}
