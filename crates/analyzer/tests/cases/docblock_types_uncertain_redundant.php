<?php

declare(strict_types=1);

final class Item
{
    public string $title = 'item';
}

/**
 * @property string $vs
 */
final class MagicRow
{
    public function __get(string $name): mixed
    {
        return $name;
    }
}

final class DocblockTypes
{
    /** @var string */
    public static $code = 'code';

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
     * @return string
     */
    public function label()
    {
        return 'label';
    }

    public function magicPropertyCast(MagicRow $row): string
    {
        return (string) $row->vs;
    }

    /**
     * @param int $id
     */
    public function parameterCast($id): int
    {
        return (int) $id;
    }

    public function returnCast(): string
    {
        return (string) $this->label();
    }

    public static function staticPropertyCast(): string
    {
        return (string) self::$code;
    }

    /**
     * @param list<array<string, mixed>> $records
     */
    public function elementCheck(array $records): int
    {
        $count = 0;
        foreach ($records as $record) {
            if (is_array($record)) {
                $count++;
            }
        }

        return $count;
    }

    public function inlineVarCoalesce(mixed $value): string
    {
        /** @var string $text */
        $text = $value;

        return $text ?? '';
    }

    /**
     * @param Item $item
     */
    public function nullsafe($item): string
    {
        return $item?->title ?? '';
    }

    public function propertyCondition(): string
    {
        if ($this->item) {
            $state = 'set';
        } else {
            $state = 'unset';
        }

        return $state;
    }

    /**
     * @param Item $item
     */
    public function comparison($item, bool $flag): bool
    {
        return $flag && $item !== null;
    }
}

final class NativeTypes
{
    public static string $code = 'code';

    private Item $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    public function cast(string $text): string
    {
        return (string) $text; // @mago-expect analysis:redundant-cast
    }

    public static function staticPropertyCast(): string
    {
        return (string) self::$code; // @mago-expect analysis:redundant-cast
    }

    /**
     * @param array<string, mixed> $record
     */
    public function elementCheck(array $record): bool
    {
        return is_array($record); // @mago-expect analysis:redundant-type-comparison
    }

    public function coalesce(string $text): string
    {
        return $text ?? ''; // @mago-expect analysis:redundant-null-coalesce
    }

    public function nullsafe(Item $item): string
    {
        return $item?->title; // @mago-expect analysis:redundant-nullsafe-operator
    }

    public function propertyCondition(): string
    {
        if ($this->item) { // @mago-expect analysis:redundant-condition
            $state = 'set';
        } else { // @mago-expect analysis:unreachable-else-clause
            $state = 'unset';
        }

        return $state;
    }

    public function comparison(Item $item, bool $flag): bool
    {
        return $flag && $item !== null; // @mago-expect analysis:redundant-comparison,redundant-logical-operation
    }
}

function docblock_static_property_cast(): string
{
    return (string) DocblockTypes::$code;
}

function native_static_property_cast(): string
{
    return (string) NativeTypes::$code; // @mago-expect analysis:redundant-cast
}
