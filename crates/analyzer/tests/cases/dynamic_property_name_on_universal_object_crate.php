<?php

declare(strict_types=1);

interface Row
{
}

final class TableRow implements Row
{
}

final class Entity
{
}

final class Payload extends stdClass
{
}

function read_child(SimpleXMLElement $parent, string $name): ?SimpleXMLElement
{
    return $parent->{$name} instanceof SimpleXMLElement ? $parent->{$name} : null;
}

function read_std(stdClass $object, string $name): mixed
{
    return $object->{$name};
}

function read_payload(Payload $object, string $name): mixed
{
    return $object->{$name};
}

function read_row(TableRow $row, string $name): mixed
{
    return $row->{$name};
}

function read_possibly_missing_row(?TableRow $row, string $name): mixed
{
    /** @mago-expect analysis:string-member-selector */
    return $row->{$name};
}

function read_row_or_entity(TableRow|Entity $object, string $name): mixed
{
    return $object->{$name};
}

function read_nullsafe(?Entity $entity, string $name): mixed
{
    return $entity?->{$name};
}

function read_entity(Entity $entity, string $name): mixed
{
    /** @mago-expect analysis:string-member-selector */
    return $entity->{$name};
}

function call_on_std(stdClass $object, string $name): mixed
{
    /** @mago-expect analysis:string-member-selector,mixed-assignment */
    $result = $object->{$name}();

    return $result;
}
