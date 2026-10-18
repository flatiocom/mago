<?php

declare(strict_types=1);

final class Feed extends SimpleXMLElement
{
}

function takes_string(string $value): void
{
    echo $value;
}

function takes_feed(Feed $feed): void
{
    echo $feed->getName();
}

/**
 * @param iterable<string, SimpleXMLElement> $elements
 */
function takes_elements(iterable $elements): void
{
    foreach ($elements as $element) {
        echo $element->getName();
    }
}

function iterate_element(SimpleXMLElement $el): void
{
    foreach ($el as $key => $item) {
        takes_string($key);
        echo $item->getName();
    }

    takes_elements($el);
}

function iterate_children(SimpleXMLElement $el): void
{
    $children = $el->children();
    if ($children === null) {
        return;
    }

    foreach ($children as $item) {
        echo $item->getName();
    }
}

function iterate_subclass(Feed $feed): void
{
    foreach ($feed as $item) {
        takes_feed($item);
    }
}

function read_current(SimpleXMLElement $el): string
{
    return $el->current()->getName();
}

function read_offset(SimpleXMLElement $el): string
{
    return $el['x']->getName();
}

function read_children_unguarded(SimpleXMLElement $el): string
{
    // @mago-expect analysis:possible-method-access-on-null
    return $el->children()->getName();
}
