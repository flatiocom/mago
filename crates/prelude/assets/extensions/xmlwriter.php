<?php

function xmlwriter_open_uri(string $uri): XMLWriter|false {}

function xmlwriter_open_memory(): XMLWriter|false {}

function xmlwriter_set_indent(XMLWriter $writer, bool $enable): bool {}

function xmlwriter_set_indent_string(XMLWriter $writer, string $indentation): bool {}

function xmlwriter_start_comment(XMLWriter $writer): bool {}

function xmlwriter_end_comment(XMLWriter $writer): bool {}

function xmlwriter_start_attribute(XMLWriter $writer, string $name): bool {}

function xmlwriter_end_attribute(XMLWriter $writer): bool {}

function xmlwriter_write_attribute(XMLWriter $writer, string $name, string $value): bool {}

function xmlwriter_start_attribute_ns(XMLWriter $writer, ?string $prefix, string $name, ?string $namespace): bool {}

function xmlwriter_write_attribute_ns(
    XMLWriter $writer,
    ?string $prefix,
    string $name,
    ?string $namespace,
    string $value,
): bool {}

function xmlwriter_start_element(XMLWriter $writer, string $name): bool {}

function xmlwriter_end_element(XMLWriter $writer): bool {}

function xmlwriter_full_end_element(XMLWriter $writer): bool {}

function xmlwriter_start_element_ns(XMLWriter $writer, ?string $prefix, string $name, ?string $namespace): bool {}

function xmlwriter_write_element(XMLWriter $writer, string $name, ?string $content = null): bool {}

function xmlwriter_write_element_ns(
    XMLWriter $writer,
    ?string $prefix,
    string $name,
    ?string $namespace,
    ?string $content = null,
): bool {}

function xmlwriter_start_pi(XMLWriter $writer, string $target): bool {}

function xmlwriter_end_pi(XMLWriter $writer): bool {}

function xmlwriter_write_pi(XMLWriter $writer, string $target, string $content): bool {}

function xmlwriter_start_cdata(XMLWriter $writer): bool {}

function xmlwriter_end_cdata(XMLWriter $writer): bool {}

function xmlwriter_write_cdata(XMLWriter $writer, string $content): bool {}

function xmlwriter_text(XMLWriter $writer, string $content): bool {}

function xmlwriter_write_raw(XMLWriter $writer, string $content): bool {}

function xmlwriter_start_document(
    XMLWriter $writer,
    ?string $version = '1.0',
    ?string $encoding = null,
    ?string $standalone = null,
): bool {}

function xmlwriter_end_document(XMLWriter $writer): bool {}

function xmlwriter_write_comment(XMLWriter $writer, string $content): bool {}

function xmlwriter_start_dtd(
    XMLWriter $writer,
    string $qualifiedName,
    ?string $publicId = null,
    ?string $systemId = null,
): bool {}

function xmlwriter_end_dtd(XMLWriter $writer): bool {}

function xmlwriter_write_dtd(
    XMLWriter $writer,
    string $name,
    ?string $publicId = null,
    ?string $systemId = null,
    ?string $content = null,
): bool {}

function xmlwriter_start_dtd_element(XMLWriter $writer, string $qualifiedName): bool {}

function xmlwriter_end_dtd_element(XMLWriter $writer): bool {}

function xmlwriter_write_dtd_element(XMLWriter $writer, string $name, string $content): bool {}

function xmlwriter_start_dtd_attlist(XMLWriter $writer, string $name): bool {}

function xmlwriter_end_dtd_attlist(XMLWriter $writer): bool {}

function xmlwriter_write_dtd_attlist(XMLWriter $writer, string $name, string $content): bool {}

function xmlwriter_start_dtd_entity(XMLWriter $writer, string $name, bool $isParam): bool {}

function xmlwriter_end_dtd_entity(XMLWriter $writer): bool {}

function xmlwriter_write_dtd_entity(
    XMLWriter $writer,
    string $name,
    string $content,
    bool $isParam = false,
    ?string $publicId = null,
    ?string $systemId = null,
    ?string $notationData = null,
): bool {}

function xmlwriter_output_memory(XMLWriter $writer, bool $flush = true): string {}

function xmlwriter_flush(XMLWriter $writer, bool $empty = true): string|int {}

class XMLWriter
{
    #[TentativeType]
    public function openUri(string $uri): bool {}

    #[Mago\AvailableSince(80400)]
    public static function toUri(string $uri): static {}

    #[TentativeType]
    public function openMemory(): bool {}

    public static function toMemory(): static {}

    /** @param resource $stream */
    #[Mago\AvailableSince(80400)]
    public static function toStream($stream): static {}

    #[TentativeType]
    public function setIndent(bool $enable): bool {}

    #[TentativeType]
    public function setIndentString(string $indentation): bool {}

    #[TentativeType]
    public function startComment(): bool {}

    #[TentativeType]
    public function endComment(): bool {}

    #[TentativeType]
    public function startAttribute(string $name): bool {}

    #[TentativeType]
    public function endAttribute(): bool {}

    #[TentativeType]
    public function writeAttribute(string $name, string $value): bool {}

    #[TentativeType]
    public function startAttributeNs(?string $prefix, string $name, ?string $namespace): bool {}

    #[TentativeType]
    public function writeAttributeNs(?string $prefix, string $name, ?string $namespace, string $value): bool {}

    #[TentativeType]
    public function startElement(string $name): bool {}

    #[TentativeType]
    public function endElement(): bool {}

    #[TentativeType]
    public function fullEndElement(): bool {}

    #[TentativeType]
    public function startElementNs(?string $prefix, string $name, ?string $namespace): bool {}

    #[TentativeType]
    public function writeElement(string $name, ?string $content = null): bool {}

    #[TentativeType]
    public function writeElementNs(?string $prefix, string $name, ?string $namespace, ?string $content = null): bool {}

    #[TentativeType]
    public function startPi(string $target): bool {}

    #[TentativeType]
    public function endPi(): bool {}

    #[TentativeType]
    public function writePi(string $target, string $content): bool {}

    #[TentativeType]
    public function startCdata(): bool {}

    #[TentativeType]
    public function endCdata(): bool {}

    #[TentativeType]
    public function writeCdata(string $content): bool {}

    #[TentativeType]
    public function text(string $content): bool {}

    #[TentativeType]
    public function writeRaw(string $content): bool {}

    #[TentativeType]
    public function startDocument(
        ?string $version = '1.0',
        ?string $encoding = null,
        ?string $standalone = null,
    ): bool {}

    #[TentativeType]
    public function endDocument(): bool {}

    #[TentativeType]
    public function writeComment(string $content): bool {}

    #[TentativeType]
    public function startDtd(string $qualifiedName, ?string $publicId = null, ?string $systemId = null): bool {}

    #[TentativeType]
    public function endDtd(): bool {}

    #[TentativeType]
    public function writeDtd(
        string $name,
        ?string $publicId = null,
        ?string $systemId = null,
        ?string $content = null,
    ): bool {}

    #[TentativeType]
    public function startDtdElement(string $qualifiedName): bool {}

    #[TentativeType]
    public function endDtdElement(): bool {}

    #[TentativeType]
    public function writeDtdElement(string $name, string $content): bool {}

    #[TentativeType]
    public function startDtdAttlist(string $name): bool {}

    #[TentativeType]
    public function endDtdAttlist(): bool {}

    #[TentativeType]
    public function writeDtdAttlist(string $name, string $content): bool {}

    #[TentativeType]
    public function startDtdEntity(string $name, bool $isParam): bool {}

    #[TentativeType]
    public function endDtdEntity(): bool {}

    #[TentativeType]
    public function writeDtdEntity(
        string $name,
        string $content,
        bool $isParam = false,
        ?string $publicId = null,
        ?string $systemId = null,
        ?string $notationData = null,
    ): bool {}

    #[TentativeType]
    public function outputMemory(bool $flush = true): string {}

    #[TentativeType]
    public function flush(bool $empty = true): string|int {}
}
