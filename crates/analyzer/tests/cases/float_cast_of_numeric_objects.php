<?php

declare(strict_types=1);

function float_cast_of_simple_xml_element(SimpleXMLElement $element): float
{
    return (float) $element;
}

final class FloatCastXmlElement extends SimpleXMLElement
{
}

function float_cast_of_simple_xml_subclass(FloatCastXmlElement $element): float
{
    return (float) $element;
}

function float_cast_of_gmp(GMP $number): float
{
    return (float) $number;
}

function float_cast_of_other_object(stdClass $object): float
{
    /** @mago-expect analysis:invalid-type-cast */
    return (float) $object;
}

function float_cast_of_simple_xml_element_or_other_object(SimpleXMLElement|stdClass $object): float
{
    /** @mago-expect analysis:invalid-type-cast */
    return (float) $object;
}
