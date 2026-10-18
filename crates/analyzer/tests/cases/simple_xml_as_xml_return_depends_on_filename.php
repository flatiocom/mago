<?php

declare(strict_types=1);

/** @return array{content: string} */
function document(SimpleXMLElement $xml, string $fallback): array
{
    $content = $xml->asXML();

    return ['content' => $content === false ? $fallback : $content];
}

function save(SimpleXMLElement $xml): bool
{
    return $xml->asXML('/tmp/document.xml');
}

function document_or_false(SimpleXMLElement $xml): string
{
    /** @mago-expect analysis:falsable-return-statement,invalid-return-statement */
    return $xml->asXML();
}

function saved_as_string(SimpleXMLElement $xml): string
{
    /** @mago-expect analysis:invalid-return-statement */
    return $xml->asXML('/tmp/document.xml');
}
