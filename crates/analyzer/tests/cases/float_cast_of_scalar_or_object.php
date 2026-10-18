<?php

declare(strict_types=1);

function float_cast_of_int_or_object(int|stdClass $value): float
{
    /** @mago-expect analysis:invalid-type-cast */
    return (float) $value;
}

function float_cast_of_object_or_int(stdClass|int $value): float
{
    /** @mago-expect analysis:invalid-type-cast */
    return (float) $value;
}

/**
 * @param numeric-string|stdClass $value
 */
function float_cast_of_numeric_string_or_object(string|stdClass $value): float
{
    /** @mago-expect analysis:invalid-type-cast */
    return (float) $value;
}

/**
 * @param resource|stdClass $value
 */
function float_cast_of_resource_or_object(mixed $value): float
{
    /** @mago-expect analysis:invalid-type-cast(2) */
    return (float) $value;
}

function float_cast_of_int_or_simple_xml_element(int|SimpleXMLElement $value): float
{
    return (float) $value;
}

function float_cast_of_int_or_literal(int|bool $value): float
{
    return (float) $value;
}
