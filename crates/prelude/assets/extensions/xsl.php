<?php

/**
 * @var int
 */
const XSL_CLONE_AUTO = 0;

/**
 * @var int
 */
const XSL_CLONE_NEVER = -1;

/**
 * @var int
 */
const XSL_CLONE_ALWAYS = 1;

/**
 * @var int
 */
const XSL_SECPREF_NONE = UNKNOWN;

/**
 * @var int
 */
const XSL_SECPREF_READ_FILE = UNKNOWN;

/**
 * @var int
 */
const XSL_SECPREF_WRITE_FILE = UNKNOWN;

/**
 * @var int
 */
const XSL_SECPREF_CREATE_DIRECTORY = UNKNOWN;

/**
 * @var int
 */
const XSL_SECPREF_READ_NETWORK = UNKNOWN;

/**
 * @var int
 */
const XSL_SECPREF_WRITE_NETWORK = UNKNOWN;

/**
 * @var int
 */
const XSL_SECPREF_DEFAULT = UNKNOWN;

/**
 * @var int
 */
const LIBXSLT_VERSION = UNKNOWN;

/**
 * @var string
 */
const LIBXSLT_DOTTED_VERSION = UNKNOWN;

/**
 * @var int
 */
const LIBEXSLT_VERSION = UNKNOWN;

/**
 * @var string
 */
const LIBEXSLT_DOTTED_VERSION = UNKNOWN;

class XSLTProcessor
{
    public bool $doXInclude = false;

    public bool $cloneDocument = false;

    public int $maxTemplateDepth;

    public int $maxTemplateVars;

    /**
     * @param DOMDocument|DOM\Document|SimpleXMLElement $stylesheet
     */
    #[TentativeType]
    public function importStylesheet(object $stylesheet): bool {}

    /**
     * @param DOMDocument|DOM\Document|SimpleXMLElement $document
     */
    #[TentativeType]
    public function transformToDoc(object $document, ?string $returnClass = null): object|false {}

    /**
     * @param DOMDocument|DOM\Document|SimpleXMLElement $document
     */
    #[TentativeType]
    public function transformToUri(object $document, string $uri): int {}

    /**
     * @param DOMDocument|DOM\Document|SimpleXMLElement $document
     */
    #[TentativeType]
    public function transformToXml(object $document): string|null|false {}

    #[TentativeType]
    public function setParameter(string $namespace, array|string $name, ?string $value = null): bool {}

    #[TentativeType]
    public function getParameter(string $namespace, string $name): string|false {}

    #[TentativeType]
    public function removeParameter(string $namespace, string $name): bool {}

    #[TentativeType]
    public function hasExsltSupport(): bool {}

    #[TentativeType]
    public function registerPHPFunctions(array|string|null $functions = null): void {}

    public function registerPHPFunctionNS(string $namespaceURI, string $name, callable $callable): void {}

    #[TentativeType]
    public function setProfiling(?string $filename): true {}

    #[TentativeType]
    public function setSecurityPrefs(int $preferences): int {}

    #[TentativeType]
    public function getSecurityPrefs(): int {}
}
