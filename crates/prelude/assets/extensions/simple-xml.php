<?php

/**
 * @implements Traversable<string, static>
 * @implements ArrayAccess<int|string, static|null>
 * @implements Iterator<string, static>
 * @implements RecursiveIterator<string, static>
 */
class SimpleXMLElement implements Traversable, ArrayAccess, Countable, Iterator, Stringable, RecursiveIterator
{
    /**
     * @throws Exception
     */
    public function __construct(
        string $data,
        int $options = 0,
        bool $dataIsURL = false,
        string $namespaceOrPrefix = '',
        bool $isPrefix = false,
    ) {}

    /**
     * @param string $name child name
     *
     * @return static
     */
    private function __get($name) {}

    #[TentativeType]
    public function asXML(?string $filename = null): string|bool {}

    #[TentativeType]
    public function saveXML(?string $filename = null): string|bool {}

    /**
     * @return list<SimpleXMLElement>|false|null
     */
    #[TentativeType]
    public function xpath(string $expression): array|false|null {}

    #[TentativeType]
    public function registerXPathNamespace(string $prefix, string $namespace): bool {}

    #[TentativeType]
    public function attributes(?string $namespaceOrPrefix = null, bool $isPrefix = false): ?static {}

    #[TentativeType]
    public function children(?string $namespaceOrPrefix = null, bool $isPrefix = false): ?static {}

    /**
     * @return array<string, string>
     */
    #[TentativeType]
    public function getNamespaces(bool $recursive = false): array {}

    /**
     * @return array<string, string>|false
     */
    #[TentativeType]
    public function getDocNamespaces(bool $recursive = false, bool $fromRoot = true): array|false {}

    #[TentativeType]
    public function getName(): string {}

    #[TentativeType]
    public function addChild(string $qualifiedName, ?string $value = null, ?string $namespace = null): ?static {}

    #[TentativeType]
    public function addAttribute(string $qualifiedName, string $value, ?string $namespace = null): void {}

    public function __toString(): string {}

    /**
     * @return int<0, max>
     */
    #[TentativeType]
    public function count(): int {}

    /**
     * @param string|int $offset
     *
     * @return bool
     */
    public function offsetExists($offset) {}

    /**
     * @param string|int $offset
     *
     * @return static|null
     */
    public function offsetGet($offset) {}

    /**
     * @param string|int $offset
     *
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {}

    /**
     * @param string|int $offset
     *
     * @return void
     */
    public function offsetUnset($offset) {}

    #[TentativeType]
    public function rewind(): void {}

    #[TentativeType]
    public function valid(): bool {}

    #[TentativeType]
    public function current(): static {}

    #[TentativeType]
    public function key(): string {}

    #[TentativeType]
    public function next(): void {}

    #[TentativeType]
    public function hasChildren(): bool {}

    #[TentativeType]
    public function getChildren(): ?SimpleXMLElement {}
}

class SimpleXMLIterator extends SimpleXMLElement implements RecursiveIterator, Countable, Stringable
{
    /**
     * @return void
     */
    public function rewind() {}

    /**
     * @return bool
     */
    public function valid() {}

    /**
     * @return static|null
     */
    public function current() {}

    /**
     * @return string|false
     */
    public function key() {}

    /**
     * @return void
     */
    public function next() {}

    /**
     * @return bool
     */
    public function hasChildren() {}

    /**
     * @return SimpleXMLIterator
     */
    public function getChildren() {}

    /**
     * @return string
     */
    public function __toString() {}

    public function count() {}
}

/**
 * @template T of SimpleXMLElement
 *
 * @param class-string<T>|null $class_name
 *
 * @return T|false
 */
function simplexml_load_file(
    string $filename,
    ?string $class_name = SimpleXMLElement::class,
    int $options = 0,
    string $namespace_or_prefix = '',
    bool $is_prefix = false,
): SimpleXMLElement|false {}

/**
 * @template T of SimpleXMLElement
 *
 * @param class-string<T>|null $class_name
 *
 * @return T|false
 */
function simplexml_load_string(
    string $data,
    ?string $class_name = SimpleXMLElement::class,
    int $options = 0,
    string $namespace_or_prefix = '',
    bool $is_prefix = false,
): SimpleXMLElement|false {}

/**
 * @template T of SimpleXMLElement
 *
 * @param class-string<T>|null $class_name
 *
 * @return T|null
 */
function simplexml_import_dom(object $node, ?string $class_name = SimpleXMLElement::class): ?SimpleXMLElement {}
