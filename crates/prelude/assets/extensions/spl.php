<?php

class LogicException extends Exception {}

class BadFunctionCallException extends LogicException {}

class BadMethodCallException extends BadFunctionCallException {}

class DomainException extends LogicException {}

class InvalidArgumentException extends LogicException {}

class LengthException extends LogicException {}

class OutOfRangeException extends LogicException {}

class RuntimeException extends Exception {}

class OutOfBoundsException extends RuntimeException {}

class OverflowException extends RuntimeException {}

class RangeException extends RuntimeException {}

class UnderflowException extends RuntimeException {}

class UnexpectedValueException extends RuntimeException {}

/**
 * @template-implements Iterator<never, never>
 */
class EmptyIterator implements Iterator
{
    /**
     * @return never
     */
    public function current() {}

    /**
     * @return never
     */
    public function key() {}

    /**
     * @return void
     */
    public function next() {}

    /**
     * @return void
     */
    public function rewind() {}

    /**
     * @return false
     */
    public function valid() {}
}

/**
 * @template-covariant K
 * @template-covariant V
 * @template-covariant TIterator as Iterator<K, V>
 *
 * @template-implements OuterIterator<K, V>
 *
 * @template-extends FilterIterator<K, V, TIterator>
 */
class CallbackFilterIterator extends FilterIterator implements OuterIterator
{
    /**
     * @param TIterator $iterator
     * @param (callable(V, K, TIterator): bool) $callback
     */
    public function __construct(Iterator $iterator, callable $callback) {}

    #[TentativeType]
    public function accept(): bool {}

    /**
     * @return V|null
     * @ignore-nullable-return
     */
    public function current() {}

    /**
     * @return K|null
     * @ignore-nullable-return
     */
    public function key() {}
}

/**
 * @template K
 * @template V
 * @template TIterator as RecursiveIterator<K, V>
 *
 * @template-implements RecursiveIterator<K, V>
 * @template-extends CallbackFilterIterator<K, V, TIterator>
 */
class RecursiveCallbackFilterIterator extends CallbackFilterIterator implements RecursiveIterator
{
    /**
     * @param TIterator $iterator
     * @param (callable(V, K, TIterator): bool) $callback
     */
    public function __construct(RecursiveIterator $iterator, callable $callback) {}

    /**
     * @return RecursiveCallbackFilterIterator<K, V, TIterator>
     */
    public function getChildren() {}

    /**
     * @return bool
     */
    public function hasChildren() {}

    /**
     * @return V|null
     */
    public function current() {}

    /**
     * @return K|null
     */
    public function key() {}
}

/**
 * @template-covariant K
 * @template-covariant V
 *
 * @template-extends Iterator<K, V>
 */
interface RecursiveIterator extends Iterator
{
    /**
     * @return bool
     */
    public function hasChildren();

    /**
     * @return RecursiveIterator<K, V>
     */
    public function getChildren();
}

/**
 * @template K
 * @template-covariant V
 *
 * @implements OuterIterator<K, V>
 */
class RecursiveIteratorIterator implements OuterIterator
{
    public const LEAVES_ONLY = 0;
    public const SELF_FIRST = 1;
    public const CHILD_FIRST = 2;
    public const CATCH_GET_CHILD = 16;

    /**
     * @param Traversable<K, V> $iterator
     */
    public function __construct(Traversable $iterator, int $mode = self::LEAVES_ONLY, int $flags = 0) {}

    #[TentativeType]
    public function rewind(): void {}

    #[TentativeType]
    public function valid(): bool {}

    /**
     * @return null|K
     */
    #[TentativeType]
    public function key(): mixed {}

    /**
     * @return null|V
     */
    #[TentativeType]
    public function current(): mixed {}

    #[TentativeType]
    public function next(): void {}

    #[TentativeType]
    public function getDepth(): int {}

    /**
     * @return RecursiveIterator<K, V>|null
     */
    #[TentativeType]
    public function getSubIterator(?int $level = null): ?RecursiveIterator {}

    /**
     * @return RecursiveIterator<K, V>
     */
    #[TentativeType]
    public function getInnerIterator(): RecursiveIterator {}

    #[TentativeType]
    public function beginIteration(): void {}

    #[TentativeType]
    public function endIteration(): void {}

    #[TentativeType]
    public function callHasChildren(): bool {}

    /**
     * @return RecursiveIterator<K, V>|null
     */
    #[TentativeType]
    public function callGetChildren(): ?RecursiveIterator {}

    #[TentativeType]
    public function beginChildren(): void {}

    #[TentativeType]
    public function endChildren(): void {}

    #[TentativeType]
    public function nextElement(): void {}

    #[TentativeType]
    public function setMaxDepth(int $maxDepth = -1): void {}

    #[TentativeType]
    public function getMaxDepth(): int|false {}
}

/**
 * @template-covariant K
 * @template-covariant V
 *
 * @template-extends Iterator<K, V>
 */
interface OuterIterator extends Iterator
{
    /**
     * @return Iterator<K, V>
     */
    public function getInnerIterator();
}

/**
 * @template K
 * @template-covariant V
 * @template I of Traversable<K, V>
 *
 * @implements OuterIterator<K, V>
 *
 * @mixin I
 */
class IteratorIterator implements OuterIterator
{
    /**
     * @param I $iterator
     * @param class-string|null $class
     */
    public function __construct(Traversable $iterator, ?string $class = null) {}

    /**
     * @return Iterator<K, V>|null
     */
    #[TentativeType]
    public function getInnerIterator(): ?Iterator {}

    /**
     * @return void
     */
    #[TentativeType]
    public function rewind(): void {}

    #[TentativeType]
    public function valid(): bool {}

    /**
     * @return null|K
     */
    #[TentativeType]
    public function key(): mixed {}

    /**
     * @return null|V
     */
    #[TentativeType]
    public function current(): mixed {}

    #[TentativeType]
    public function next(): void {}

    public function __call(string $name, array $argv): mixed {}
}

/**
 * @template-covariant K
 * @template-covariant V
 * @template-covariant TIterator as Traversable<K, V>
 *
 * @template-extends IteratorIterator<K, V, TIterator>
 */
abstract class FilterIterator extends IteratorIterator
{
    /** @return bool */
    abstract public function accept();

    /**
     * @return null|V
     * @ignore-nullable-return
     */
    public function current() {}

    /**
     * @return null|K
     * @ignore-nullable-return
     */
    public function key() {}
}

/**
 * @template K
 * @template V
 * @template TIterator as RecursiveIterator<K, V>
 *
 * @template-extends FilterIterator<K, V, TIterator>
 * @template-implements RecursiveIterator<K, V>
 */
abstract class RecursiveFilterIterator extends FilterIterator implements RecursiveIterator
{
    /**
     * @param TIterator $iterator
     */
    public function __construct(RecursiveIterator $iterator) {}

    /**
     * @return TIterator
     */
    public function getChildren() {}

    /**
     * @return bool
     */
    public function hasChildren() {}

    /**
     * @return V|null
     * @ignore-nullable-return
     */
    public function current() {}

    /**
     * @return K|null
     * @ignore-nullable-return
     */
    public function key() {}
}

/**
 * @template K
 * @template V
 * @template TIterator as RecursiveIterator<K, V>
 *
 * @template-extends RecursiveFilterIterator<K, V, TIterator>
 */
class ParentIterator extends RecursiveFilterIterator implements RecursiveIterator, OuterIterator
{
    /**
     * @return bool
     */
    public function accept() {}

    /**
     * @param TIterator $iterator
     */
    public function __construct(RecursiveIterator $iterator) {}

    /**
     * @return ParentIterator<K,V>
     */
    public function getChildren() {}

    /**
     * @return bool
     */
    public function hasChildren() {}

    /**
     * @return void
     */
    public function next() {}

    /**
     * @return void
     */
    public function rewind() {}

    /**
     * @return V|null
     * @ignore-nullable-return
     */
    public function current() {}

    /**
     * @return K|null
     * @ignore-nullable-return
     */
    public function key() {}
}

/**
 * @template-covariant K
 * @template-covariant V
 *
 * @template-extends Iterator<K, V>
 */
interface SeekableIterator extends Iterator
{
    #[TentativeType]
    public function seek(int $offset): void;
}

/**
 * @template-covariant K
 * @template-covariant V
 * @template-covariant TIterator as Iterator<K, V>
 *
 * @template-implements OuterIterator<K, V>
 * @template-extends IteratorIterator<K, V, TIterator>
 */
class LimitIterator extends IteratorIterator implements OuterIterator
{
    /**
     * @param TIterator $iterator
     */
    public function __construct(Iterator $iterator, int $offset = 0, int $limit = -1) {}

    /**
     * @return V|null
     * @ignore-nullable-return
     */
    public function current() {}

    /**
     * @return K|null
     * @ignore-nullable-return
     */
    public function key() {}
}

/**
 * @template-covariant K
 * @template-covariant V
 * @template-covariant TIterator as Iterator<K, V>
 *
 * @template-implements OuterIterator<K, V>
 * @template-implements ArrayAccess<K, V>
 *
 * @template-extends IteratorIterator<K, V, TIterator>
 */
class CachingIterator extends IteratorIterator implements OuterIterator, ArrayAccess, Countable
{
    const CALL_TOSTRING = 1;
    const CATCH_GET_CHILD = 16;
    const TOSTRING_USE_KEY = 2;
    const TOSTRING_USE_CURRENT = 4;
    const TOSTRING_USE_INNER = 8;
    const FULL_CACHE = 256;

    /**
     * @param TIterator $iterator
     */
    public function __construct(Iterator $iterator, int $flags = self::CALL_TOSTRING) {}

    /** @return non-negative-int */
    #[TentativeType]
    public function count(): int {}

    /** @param K $key */
    #[TentativeType]
    public function offsetExists($key): bool {}

    /**
     * @param K $key
     *
     * @return V
     */
    #[TentativeType]
    public function offsetGet($key): mixed {}

    /**
     * @param K $key
     * @param V $value
     */
    #[TentativeType]
    public function offsetSet($key, mixed $value): void {}

    /** @param K $key */
    #[TentativeType]
    public function offsetUnset($key): void {}

    /**
     * @return bool
     */
    public function hasNext() {}

    /**
     * @return V|null
     * @ignore-nullable-return
     */
    public function current() {}

    /**
     * @return K|null
     * @ignore-nullable-return
     */
    public function key() {}

    /**
     * @return array<array-key, V>
     */
    public function getCache() {}
}

/**
 * @template K
 * @template V
 * @template TIterator as Iterator<K, V>
 *
 * @template-implements RecursiveIterator<K, V>
 * @template-extends CachingIterator<K, V, TIterator>
 */
class RecursiveCachingIterator extends CachingIterator implements RecursiveIterator
{
    /**
     * @return RecursiveCachingIterator<K,V, TIterator>
     */
    public function getChildren() {}

    /**
     * @return bool
     */
    public function hasChildren() {}
}

/**
 * @template-covariant K
 * @template-covariant V
 * @template-covariant TIterator as Iterator<K, V>
 *
 * @template-extends IteratorIterator<K, V, TIterator>
 */
class NoRewindIterator extends IteratorIterator
{
    /**
     * @param TIterator $iterator
     */
    public function __construct(Iterator $iterator) {}

    /**
     * @return V|null
     * @ignore-nullable-return
     */
    public function current() {}

    /**
     * @return K|null
     * @ignore-nullable-return
     */
    public function key() {}
}

/**
 * @template K
 * @template V
 * @template TIterator as Iterator<K, V>
 *
 * @template-extends IteratorIterator<K, V, TIterator>
 */
class AppendIterator extends IteratorIterator
{
    public function __construct() {}

    /**
     * @param TIterator $iterator
     * @return void
     */
    public function append(Iterator $iterator) {}

    /**
     * @return ArrayIterator<K, V>
     */
    public function getArrayIterator() {}

    /**
     * @return int
     */
    public function getIteratorIndex() {}

    /**
     * @return V|null
     * @ignore-nullable-return
     */
    public function current() {}

    /**
     * @return K|null
     * @ignore-nullable-return
     */
    public function key() {}
}

/**
 * @template-covariant K
 * @template-covariant V
 * @template-covariant TIterator as Iterator<K, V>
 *
 * @template-extends IteratorIterator<K, V, TIterator>
 */
class InfiniteIterator extends IteratorIterator
{
    /**
     * @param TIterator $iterator
     */
    public function __construct(Iterator $iterator) {}

    /**
     * @return V|null
     * @ignore-nullable-return
     */
    public function current() {}

    /**
     * @return K|null
     * @ignore-nullable-return
     */
    public function key() {}
}

/**
 * @template K
 * @template V
 * @template TIterator as Iterator<K, V>
 *
 * @template-extends FilterIterator<K, V, TIterator>
 */
class RegexIterator extends FilterIterator
{
    const MATCH = 0;
    const GET_MATCH = 1;
    const ALL_MATCHES = 2;
    const SPLIT = 3;
    const REPLACE = 4;
    const USE_KEY = 1;

    /**
     * @param TIterator $iterator
     * @param string $regex
     * @param RegexIterator::MATCH|RegexIterator::GET_MATCH|RegexIterator::ALL_MATCHES|RegexIterator::SPLIT|RegexIterator::REPLACE $mode
     * @param 0|RegexIterator::USE_KEY $flags
     */
    public function __construct(
        Iterator $iterator,
        string $regex,
        int $mode = self::MATCH,
        int $flags = 0,
        int $preg_flags = 0,
    ) {}

    #[TentativeType]
    public function accept(): bool {}

    /**
     * @return null|V
     * @ignore-nullable-return
     */
    public function current() {}

    /**
     * @return null|K
     * @ignore-nullable-return
     */
    public function key() {}
}

/**
 * @template K
 * @template V
 * @template TIterator as RecursiveIterator<K, V>
 *
 * @template-implements RecursiveIterator<K, V>
 * @template-extends RegexIterator<K, V, TIterator>
 */
class RecursiveRegexIterator extends RegexIterator implements RecursiveIterator
{
    const MATCH = 0;
    const GET_MATCH = 1;
    const ALL_MATCHES = 2;
    const SPLIT = 3;
    const REPLACE = 4;
    const USE_KEY = 1;

    /**
     * @param TIterator $iterator
     * @param string $regex
     * @param RecursiveRegexIterator::MATH|RecursiveRegexIterator::GET_MATCH|RecursiveRegexIterator::ALL_MATCHES|RecursiveRegexIterator::SPLIT|RecursiveRegexIterator::REPLACE $mode
     * @param RecursiveRegexIterator::USE_KEY|0 $flags
     * @param int $preg_flags
     */
    public function __construct(
        RecursiveIterator $iterator,
        string $regex,
        int $mode = self::MATCH,
        int $flags = 0,
        int $preg_flags = 0,
    ) {}

    /**
     * @return RecursiveRegexIterator<K, V>
     */
    public function getChildren() {}

    #[TentativeType]
    public function hasChildren(): bool {}
}

/**
 * @template K
 * @template V
 *
 * @template-extends RecursiveIteratorIterator<K, V>
 * @template-implements OuterIterator<K, V>
 */
class RecursiveTreeIterator extends RecursiveIteratorIterator implements OuterIterator
{
    const LEAVES_ONLY = 0;
    const SELF_FIRST = 1;
    const CHILD_FIRST = 2;
    const CATCH_GET_CHILD = 16;

    const BYPASS_CURRENT = 4;
    const BYPASS_KEY = 8;
    const PREFIX_LEFT = 0;
    const PREFIX_MID_HAS_NEXT = 1;
    const PREFIX_MID_LAST = 2;
    const PREFIX_END_HAS_NEXT = 3;
    const PREFIX_END_LAST = 4;
    const PREFIX_RIGHT = 5;

    /**
     * @return void
     */
    public function beginChildren() {}

    /**
     * @return RecursiveIterator
     */
    public function beginIteration() {}

    /**
     * @return RecursiveIterator
     */
    public function callGetChildren() {}

    /**
     * @return bool
     */
    public function callHasChildren() {}

    /**
     * @param RecursiveIterator<K, V>|IteratorAggregate<K, V> $it
     * @param int $flags
     * @param RecursiveTreeIterator::CATCH_GET_CHILD $cit_flags
     * @param RecursiveTreeIterator::LEAVES_ONLY|RecursiveTreeIterator::SELF_FIRST|RecursiveTreeIterator::CHILD_FIRST $mode
     */
    public function __construct(
        $it,
        int $flags = self::BYPASS_KEY,
        int $cit_flags = self::CATCH_GET_CHILD,
        int $mode = self::SELF_FIRST,
    ) {}

    /**
     * @return null|V
     * @ignore-nullable-return
     */
    public function current() {}

    #[TentativeType]
    public function endChildren(): void {}

    #[TentativeType]
    public function endIteration(): void {}

    #[TentativeType]
    public function getEntry(): string {}

    #[TentativeType]
    public function getPostfix(): string {}

    #[TentativeType]
    public function getPrefix(): string {}

    /**
     * @return null|K
     * @ignore-nullable-return
     */
    public function key() {}

    #[TentativeType]
    public function next(): void {}

    #[TentativeType]
    public function nextElement(): void {}

    #[TentativeType]
    public function rewind(): void {}

    #[TentativeType]
    public function setPostfix(string $postfix): void {}

    #[TentativeType]
    public function setPrefixPart(int $part, string $value): void {}

    #[TentativeType]
    public function valid(): bool {}
}

/**
 * @template K of array-key
 * @template V
 *
 * @template-implements IteratorAggregate<K, V>
 * @template-implements ArrayAccess<K, V>
 */
class ArrayObject implements IteratorAggregate, ArrayAccess, Serializable, Countable
{
    const STD_PROP_LIST = 1;
    const ARRAY_AS_PROPS = 2;

    /**
     * @param array<K, V>|object $input
     * @param int $flags
     * @param class-string<ArrayIterator<K,V>>|class-string<ArrayObject<K,V>> $iterator_class
     */
    public function __construct($input = null, $flags = 0, $iterator_class = 'ArrayIterator') {}

    /**
     * @param K $offset
     *
     * @return bool
     *
     * @no-named-arguments
     */
    public function offsetExists($offset) {}

    /**
     * @param K $offset
     *
     * @return V
     *
     * @no-named-arguments
     */
    public function offsetGet($offset) {}

    /**
     * @param K $offset
     * @param V $value
     *
     * @return void
     *
     * @no-named-arguments
     */
    public function offsetSet($offset, $value) {}

    /**
     * @param K $offset
     *
     * @return void
     *
     * @no-named-arguments
     */
    public function offsetUnset($offset) {}

    /**
     * @param V $value
     *
     * @return void
     */
    public function append($value) {}

    /**
     * @return array<K, V>
     */
    public function getArrayCopy() {}

    /**
     * @return int
     */
    public function count() {}

    /**
     * @return int
     */
    public function getFlags() {}

    /**
     * @param int $flags
     *
     * @return void
     */
    public function setFlags($flags) {}

    /**
     * @return void
     */
    public function asort() {}

    /**
     * @return void
     */
    public function ksort() {}

    /**
     * @param (callable(V, V): int) $cmp_function
     *
     * @return void
     */
    public function uasort($cmp_function) {}

    /**
     * @param (callable(K, K):int) $cmp_function
     *
     * @return void
     */
    public function uksort($cmp_function) {}

    /**
     * @return void
     */
    public function natsort() {}

    /**
     * @return void
     */
    public function natcasesort() {}

    /**
     * @param string $serialized
     *
     * @return void
     */
    public function unserialize($serialized) {}

    /**
     * @return string
     */
    public function serialize() {}

    /**
     * @return ArrayIterator<K, V>
     */
    public function getIterator() {}

    /**
     * @param mixed $input
     *
     * @return array
     */
    public function exchangeArray($input) {}

    /**
     * @param class-string<ArrayIterator<K,V>>|class-string<ArrayObject<K,V>> $iterator_class
     *
     * @return void
     */
    public function setIteratorClass($iterator_class) {}

    /**
     * @return class-string<ArrayIterator<K, V>>|class-string<ArrayObject<K, V>>
     */
    public function getIteratorClass() {}
}

/**
 * @template K as array-key
 * @template V
 *
 * @template-implements SeekableIterator<K, V>
 * @template-implements ArrayAccess<K, V>
 */
class ArrayIterator implements SeekableIterator, ArrayAccess, Serializable, Countable
{
    const STD_PROP_LIST = 1;
    const ARRAY_AS_PROPS = 2;

    /**
     * @param array<K, V> $array
     * @param int $flags
     */
    public function __construct($array = [], $flags = 0) {}

    /**
     * @param K $offset
     *
     * @return bool
     */
    public function offsetExists($offset) {}

    /**
     * @param K $offset
     *
     * @return V|null
     *
     * @ignore-nullable-return
     */
    public function offsetGet($offset) {}

    /**
     * @param K $offset
     *
     * @param V $value
     *
     * @return void
     */
    public function offsetSet($offset, $value) {}

    /**
     * @param K $offset
     *
     * @return void
     */
    public function offsetUnset($offset) {}

    /**
     * @param V $value
     *
     * @return void
     */
    public function append($value) {}

    /**
     * @return array<K, V>
     */
    public function getArrayCopy() {}

    /**
     * @return int
     */
    public function count() {}

    /**
     * @return int
     */
    public function getFlags() {}

    /**
     * @param int $flags
     *
     * @return void
     */
    public function setFlags($flags) {}

    /**
     * @return void
     */
    public function asort() {}

    /**
     * @return void
     */
    public function ksort() {}

    /**
     * @param (callable(V,V): int) $cmp_function
     *
     * @return void
     */
    public function uasort($cmp_function) {}

    /**
     * @param (callable(K,K): int) $cmp_function
     *
     * @return void
     */
    public function uksort($cmp_function) {}

    /**
     * @return void
     */
    public function natsort() {}

    /**
     * @return void
     */
    public function natcasesort() {}

    /**
     * @param string $serialized
     *
     * @return void
     */
    public function unserialize($serialized) {}

    /**
     * @return string
     */
    public function serialize() {}

    /**
     * @return void
     */
    public function rewind() {}

    /**
     * @return V|null
     *
     * @ignore-nullable-return
     */
    public function current() {}

    /**
     * @return K|null
     *
     * @ignore-nullable-return
     */
    public function key() {}

    /**
     * @return void
     */
    public function next() {}

    /**
     * @return bool
     */
    public function valid() {}

    /**
     * @param int $offset
     *
     * @return void
     */
    public function seek($offset) {}
}

/**
 * @template K
 * @template V
 *
 * @template-implements RecursiveIterator<K, V>
 * @template-extends ArrayIterator<K, V>
 */
class RecursiveArrayIterator extends ArrayIterator implements RecursiveIterator
{
    const STD_PROP_LIST = 1;
    const ARRAY_AS_PROPS = 2;
    const CHILD_ARRAYS_ONLY = 4;

    /**
     * @return ?RecursiveArrayIterator<K, V>
     */
    public function getChildren() {}

    /**
     * @return bool
     */
    public function hasChildren() {}

    /**
     * @return V|null
     *
     * @ignore-nullable-return
     */
    public function current() {}

    /**
     * @return K|null
     *
     * @ignore-nullable-return
     */
    public function key() {}
}

class SplFileInfo implements Stringable
{
    public function __construct(string $filename) {}

    #[TentativeType]
    public function getPath(): string {}

    #[TentativeType]
    public function getFilename(): string {}

    #[TentativeType]
    public function getExtension(): string {}

    #[TentativeType]
    public function getBasename(string $suffix = ''): string {}

    #[TentativeType]
    public function getPathname(): string {}

    #[TentativeType]
    public function getPerms(): int|false {}

    /**
     * @throws RuntimeException
     */
    #[TentativeType]
    public function getInode(): int|false {}

    /**
     * @throws RuntimeException
     */
    #[TentativeType]
    public function getSize(): int|false {}

    /**
     * @throws RuntimeException
     */
    #[TentativeType]
    public function getOwner(): int|false {}

    /**
     * @throws RuntimeException
     */
    #[TentativeType]
    public function getGroup(): int|false {}

    /**
     * @throws RuntimeException
     */
    #[TentativeType]
    public function getATime(): int|false {}

    #[TentativeType]
    public function getMTime(): int|false {}

    /**
     * @throws RuntimeException
     */
    #[TentativeType]
    public function getCTime(): int|false {}

    /**
     * @throws RuntimeException
     */
    #[TentativeType]
    public function getType(): string|false {}

    #[TentativeType]
    public function isWritable(): bool {}

    #[TentativeType]
    public function isReadable(): bool {}

    #[TentativeType]
    public function isExecutable(): bool {}

    #[TentativeType]
    public function isFile(): bool {}

    #[TentativeType]
    public function isDir(): bool {}

    #[TentativeType]
    public function isLink(): bool {}

    /**
     * @throws RuntimeException
     */
    #[TentativeType]
    public function getLinkTarget(): string|false {}

    #[TentativeType]
    public function getRealPath(): string|false {}

    /**
     * @template T of SplFileInfo
     *
     * @param null|class-string<T> $class
     *
     * @return ($class is null ? SplFileInfo : T)
     */
    #[TentativeType]
    public function getFileInfo(?string $class = null): SplFileInfo {}

    /**
     * @template T of SplFileInfo
     *
     * @param null|class-string<T> $class
     *
     * @return ($class is null ? null|SplFileInfo : null|T)
     */
    #[TentativeType]
    public function getPathInfo(?string $class = null): ?SplFileInfo {}

    /**
     * @param null|resource $context
     *
     * @throws RuntimeException
     */
    #[TentativeType]
    public function openFile(string $mode = 'r', bool $useIncludePath = false, $context = null): SplFileObject {}

    /**
     * @template T of SplFileInfo
     *
     * @param class-string<T> $class
     */
    #[TentativeType]
    public function setFileClass(string $class = SplFileObject::class): void {}

    /**
     * @template T of SplFileInfo
     *
     * @param class-string<T> $class
     */
    #[TentativeType]
    public function setInfoClass(string $class = SplFileInfo::class): void {}

    /**
     * @return string
     */
    public function __toString(): string {}

    final public function _bad_state_ex(): void {}

    public function __wakeup() {}

    #[TentativeType]
    public function __debugInfo(): array {}
}

/**
 * @template-implements SeekableIterator<int, DirectoryIterator>
 */
class DirectoryIterator extends SplFileInfo implements SeekableIterator
{
    public function __construct(string $path) {}

    /**
     * @return null|DirectoryIterator
     *
     * @ignore-nullable-return
     */
    public function current() {}

    /**
     * @return null|int
     *
     * @ignore-nullable-return
     */
    public function key() {}

    /**
     * @return void
     */
    public function next() {}

    /**
     * @return void
     */
    public function rewind() {}

    /**
     * @param int $offset
     */
    public function seek($offset) {}

    /**
     * @return bool
     */
    public function valid() {}

    #[TentativeType]
    public function isDot(): bool {}
}

/**
 * @implements Iterator<string, string|SplFileInfo>
 */
class FilesystemIterator extends DirectoryIterator implements Iterator
{
    const CURRENT_AS_PATHNAME = 32;
    const CURRENT_AS_FILEINFO = 0;
    const CURRENT_AS_SELF = 16;
    const CURRENT_MODE_MASK = 240;
    const KEY_AS_PATHNAME = 0;
    const KEY_AS_FILENAME = 256;
    const FOLLOW_SYMLINKS = 512;
    const KEY_MODE_MASK = 3840;
    const NEW_CURRENT_AND_KEY = 256;
    const SKIP_DOTS = 4096;
    const UNIX_PATHS = 8192;

    public function __construct(
        string $path,
        int $flags = self::KEY_AS_PATHNAME | self::CURRENT_AS_FILEINFO | self::SKIP_DOTS,
    ) {}

    /**
     * @return string|SplFileInfo|null
     *
     * @ignore-nullable-return
     */
    #[TentativeType]
    public function current(): string|SplFileInfo|null {}

    /**
     * @return string|null
     *
     * @ignore-nullable-return
     */
    public function key() {}

    /**
     * @return int
     */
    public function getFlags() {}

    /**
     * @param int $flags
     *
     * @return void
     */
    public function setFlags($flags) {}
}

/**
 * @template-implements RecursiveIterator<string, RecursiveDirectoryIterator|string|SplFileInfo>
 * @template-implements SeekableIterator<string, RecursiveDirectoryIterator|string|SplFileInfo>
 */
class RecursiveDirectoryIterator extends FilesystemIterator implements RecursiveIterator, SeekableIterator
{
    const CURRENT_AS_PATHNAME = 32;
    const CURRENT_AS_FILEINFO = 0;
    const CURRENT_AS_SELF = 16;
    const CURRENT_MODE_MASK = 240;
    const KEY_AS_PATHNAME = 0;
    const KEY_AS_FILENAME = 256;
    const FOLLOW_SYMLINKS = 512;
    const KEY_MODE_MASK = 3840;
    const NEW_CURRENT_AND_KEY = 256;
    const SKIP_DOTS = 4096;
    const UNIX_PATHS = 8192;

    public function __construct(string $path, int $flags = self::KEY_AS_PATHNAME | self::CURRENT_AS_FILEINFO) {}

    #[TentativeType]
    public function getChildren(): RecursiveDirectoryIterator {}

    #[TentativeType]
    public function hasChildren(bool $allowLinks = false): bool {}

    #[TentativeType]
    public function getSubPath(): string {}

    #[TentativeType]
    public function getSubPathname(): string {}

    /**
     * @return RecursiveDirectoryIterator|string|SplFileInfo|null
     *
     * @ignore-nullable-return
     */
    public function current() {}

    /**
     * @return string|null
     *
     * @ignore-nullable-return
     */
    public function key() {}
}

/**
 * @template-extends FilesystemIterator<string, GlobIterator|SplFileInfo|string>
 */
class GlobIterator extends FilesystemIterator implements Countable
{
    /**
     * @return int<0, max>
     */
    public function count() {}
}

class SplFileObject extends SplFileInfo implements RecursiveIterator, SeekableIterator
{
    public const DROP_NEW_LINE = 1;
    public const READ_AHEAD = 2;
    public const SKIP_EMPTY = 4;
    public const READ_CSV = 8;

    /**
     * @param resource|null $context
     *
     * @throws RuntimeException
     * @throws LogicException
     */
    public function __construct(string $filename, string $mode = 'r', bool $useIncludePath = false, $context = null) {}

    /**
     * @throws RuntimeException
     */
    #[TentativeType]
    public function rewind(): void {}

    #[TentativeType]
    public function eof(): bool {}

    #[TentativeType]
    public function valid(): bool {}

    /**
     * @throws RuntimeException
     */
    #[TentativeType]
    public function fgets(): string {}

    #[TentativeType]
    public function fread(int $length): string|false {}

    /**
     * @return array|false|null
     */
    public function fgetcsv(string $separator = ',', string $enclosure = "\"", string $escape = "\\") {}

    #[TentativeType]
    public function fputcsv(
        array $fields,
        string $separator = ',',
        string $enclosure = '"',
        string $escape = "\\",
        string $eol = PHP_EOL,
    ): int|false {}

    #[TentativeType]
    public function setCsvControl(string $separator = ',', string $enclosure = "\"", string $escape = "\\"): void {}

    #[TentativeType]
    public function getCsvControl(): array {}

    #[TentativeType]
    public function flock(int $operation, int &$wouldBlock = null): bool {}

    #[TentativeType]
    public function fflush(): bool {}

    #[TentativeType]
    public function ftell(): int|false {}

    #[TentativeType]
    public function fseek(int $offset, int $whence = SEEK_SET): int {}

    #[TentativeType]
    public function fgetc(): string|false {}

    #[TentativeType]
    public function fpassthru(): int {}

    #[TentativeType]
    public function fscanf(string $format, mixed &...$vars): array|int|null {}

    #[TentativeType]
    public function fwrite(string $data, int $length = 0): int|false {}

    #[TentativeType]
    public function fstat(): array {}

    #[TentativeType]
    public function ftruncate(int $size): bool {}

    #[TentativeType]
    public function current(): string|array|false {}

    #[TentativeType]
    public function key(): int {}

    #[TentativeType]
    public function next(): void {}

    #[TentativeType]
    public function setFlags(int $flags): void {}

    #[TentativeType]
    public function getFlags(): int {}

    /**
     * @throws DomainException
     */
    #[TentativeType]
    public function setMaxLineLen(int $maxLength): void {}

    /**
     * @return int<0, max>
     */
    #[TentativeType]
    public function getMaxLineLen(): int {}

    /**
     * @return bool
     */
    public function hasChildren() {}

    /**
     * @return null|RecursiveIterator
     */
    public function getChildren() {}

    /**
     * @throws LogicException
     */
    #[TentativeType]
    public function seek(int $line): void {}

    #[TentativeType]
    public function getCurrentLine(): string {}

    public function __toString(): string {}
}

class SplTempFileObject extends SplFileObject
{
    /**
     * @throws RuntimeException
     */
    public function __construct(int $maxMemory = 2097152) {}
}

/**
 * @template V
 * @template-implements Iterator<int, V>
 * @template-implements ArrayAccess<int, V>
 */
class SplDoublyLinkedList implements Iterator, Countable, ArrayAccess, Serializable
{
    public const IT_MODE_LIFO = 2;
    public const IT_MODE_FIFO = 0;
    public const IT_MODE_DELETE = 1;
    public const IT_MODE_KEEP = 0;

    /**
     * @param V $value
     */
    #[TentativeType]
    public function add(int $index, mixed $value): void {}

    /**
     * @return V
     */
    #[TentativeType]
    public function pop(): mixed {}

    /**
     * @return V
     */
    #[TentativeType]
    public function shift(): mixed {}

    /**
     * @param V $value
     */
    #[TentativeType]
    public function push(mixed $value): void {}

    /**
     * @param V $value
     */
    #[TentativeType]
    public function unshift(mixed $value): void {}

    /**
     * @return V
     */
    #[TentativeType]
    public function top(): mixed {}

    /**
     * @return V
     */
    #[TentativeType]
    public function bottom(): mixed {}

    #[TentativeType]
    public function count(): int {}

    #[TentativeType]
    public function isEmpty(): bool {}

    #[TentativeType]
    public function setIteratorMode(int $mode): int {}

    #[TentativeType]
    public function getIteratorMode(): int {}

    #[TentativeType]
    public function offsetExists($index): bool {}

    /**
     * @return V
     */
    #[TentativeType]
    public function offsetGet($index): mixed {}

    /**
     * @param V $value
     */
    #[TentativeType]
    public function offsetSet($index, mixed $value): void {}

    #[TentativeType]
    public function offsetUnset($index): void {}

    #[TentativeType]
    public function rewind(): void {}

    /**
     * @return V
     */
    #[TentativeType]
    public function current(): mixed {}

    #[TentativeType]
    public function key(): int {}

    #[TentativeType]
    public function next(): void {}

    #[TentativeType]
    public function prev(): void {}

    #[TentativeType]
    public function valid(): bool {}

    #[TentativeType]
    public function unserialize(string $data): void {}

    #[TentativeType]
    public function serialize(): string {}

    #[TentativeType]
    public function __debugInfo(): array {}

    #[TentativeType]
    public function __serialize(): array {}

    #[TentativeType]
    public function __unserialize(array $data): void {}
}

/**
 * @template V
 */
class SplQueue extends SplDoublyLinkedList
{
    /**
     * @param V $value
     */
    #[TentativeType]
    public function enqueue(mixed $value): void {}

    /**
     * @return V
     */
    #[TentativeType]
    public function dequeue(): mixed {}

    /**
     * @param int $mode
     *
     * @return void
     */
    public function setIteratorMode($mode) {}
}

/**
 * @template V
 * @template-extends SplDoublyLinkedList<V>
 */
class SplStack extends SplDoublyLinkedList
{
    /**
     * @param int $mode
     *
     * @return void
     */
    public function setIteratorMode($mode) {}
}

/**
 * @template V
 * @template-implements Iterator<int, V>
 */
abstract class SplHeap implements Iterator, Countable
{
    /**
     * @return V
     */
    #[TentativeType]
    public function extract(): mixed {}

    /**
     * @param V $value
     */
    #[TentativeType]
    public function insert(mixed $value): bool {}

    /**
     * @return V
     */
    #[TentativeType]
    public function top(): mixed {}

    #[TentativeType]
    public function count(): int {}

    #[TentativeType]
    public function isEmpty(): bool {}

    #[TentativeType]
    public function rewind(): void {}

    /**
     * @return V
     */
    #[TentativeType]
    public function current(): mixed {}

    #[TentativeType]
    public function key(): int {}

    #[TentativeType]
    public function next(): void {}

    #[TentativeType]
    public function valid(): bool {}

    #[TentativeType]
    public function recoverFromCorruption(): bool {}

    /**
     * @param V $value1
     * @param V $value2
     *
     * @return int
     */
    abstract protected function compare($value1, $value2);

    #[TentativeType]
    public function isCorrupted(): bool {}

    #[TentativeType]
    public function __debugInfo(): array {}
}

/**
 * @template V
 * @template-extends SplHeap<V>
 */
class SplMinHeap extends SplHeap
{
    /**
     * @param V $value1
     * @param V $value2
     *
     * @return int
     */
    #[TentativeType]
    protected function compare(mixed $value1, mixed $value2): int {}

    /**
     * @return V
     */
    public function extract() {}

    /**
     * @param V $value
     *
     * @return true
     */
    public function insert($value) {}

    /**
     * @return V
     */
    public function top() {}

    /**
     * @return int
     */
    public function count() {}

    /**
     * @return bool
     */
    public function isEmpty() {}

    /**
     * @return void
     */
    public function rewind() {}

    /**
     * @return V
     */
    public function current() {}

    /**
     * @return int
     */
    public function key() {}

    /**
     * @return void
     */
    public function next() {}

    /**
     * @return bool
     */
    public function valid() {}

    /**
     * @return void
     */
    public function recoverFromCorruption() {}
}

/**
 * @template V
 * @template-extends SplHeap<V>
 */
class SplMaxHeap extends SplHeap
{
    /**
     * @param V $value1
     * @param V $value2
     *
     * @return int
     */
    #[TentativeType]
    protected function compare(mixed $value1, mixed $value2): int {}
}

/**
 * @template TPriority
 * @template V
 *
 * @template-implements Iterator<int, V>
 */
class SplPriorityQueue implements Iterator, Countable
{
    public const EXTR_BOTH = 3;
    public const EXTR_PRIORITY = 2;
    public const EXTR_DATA = 1;

    /**
     * @param TPriority $priority1
     * @param TPriority $priority2
     */
    #[TentativeType]
    public function compare(mixed $priority1, mixed $priority2): int {}

    /**
     * @param V $value
     * @param TPriority $priority
     */
    #[TentativeType]
    public function insert(mixed $value, mixed $priority): true {}

    #[TentativeType]
    public function setExtractFlags(int $flags): int {}

    /**
     * @return V
     */
    #[TentativeType]
    public function top(): mixed {}

    /**
     * @return V
     */
    #[TentativeType]
    public function extract(): mixed {}

    #[TentativeType]
    public function count(): int {}

    #[TentativeType]
    public function isEmpty(): bool {}

    #[TentativeType]
    public function rewind(): void {}

    /**
     * @return V
     */
    #[TentativeType]
    public function current(): mixed {}

    /**
     * @return int
     */
    #[TentativeType]
    public function key(): int {}

    /**
     * @return void
     */
    #[TentativeType]
    public function next(): void {}

    /**
     * @return bool
     */
    #[TentativeType]
    public function valid(): bool {}

    #[TentativeType]
    public function recoverFromCorruption(): true {}

    #[TentativeType]
    public function isCorrupted(): bool {}

    #[TentativeType]
    public function getExtractFlags(): int {}

    #[TentativeType]
    public function __debugInfo(): array {}
}

/**
 * @template V
 *
 * @template-implements Iterator<int, V>
 * @template-implements ArrayAccess<int, V>
 * @template-implements IteratorAggregate<int, V>
 */
class SplFixedArray implements Iterator, ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    public function __construct(int $size = 0) {}

    /**
     * @return int<0, max>
     */
    #[TentativeType]
    public function count(): int {}

    /**
     * @return list<V>
     */
    #[TentativeType]
    public function toArray(): array {}

    #[TentativeType]
    public static function fromArray(array $array, bool $preserveKeys = true): SplFixedArray {}

    /**
     * @return int<0, max>
     */
    #[TentativeType]
    public function getSize(): int {}

    /**
     * @return bool
     */
    public function setSize(int $size) {}

    /**
     * @param int $index
     */
    #[TentativeType]
    public function offsetExists($index): bool {}

    /**
     * @param int $index
     *
     * @return V
     */
    #[TentativeType]
    public function offsetGet($index): mixed {}

    /**
     * @param int $index
     * @param V $value
     */
    #[TentativeType]
    public function offsetSet($index, mixed $value): void {}

    /**
     * @param int $index
     */
    #[TentativeType]
    public function offsetUnset($index): void {}

    /**
     * @return void
     */
    public function rewind() {}

    /**
     * @return v
     */
    public function current() {}

    /**
     * @return int
     */
    public function key() {}

    /**
     * @return void
     */
    public function next() {}

    /**
     * @return bool
     */
    #[TentativeType]
    public function valid(): bool {}

    #[TentativeType]
    #[Deprecated('The function is deprecated', since: '8.4')]
    public function __wakeup(): void {}

    public function __serialize(): array {}

    public function __unserialize(array $data): void {}

    /**
     * @return Iterator<int, V>
     */
    public function getIterator(): Iterator {}

    public function jsonSerialize(): array {}
}

interface SplObserver
{
    #[TentativeType]
    public function update(SplSubject $subject): void;
}

interface SplSubject
{
    #[TentativeType]
    public function attach(SplObserver $observer): void;

    #[TentativeType]
    public function detach(SplObserver $observer): void;

    #[TentativeType]
    public function notify(): void;
}

/**
 * @template O of object
 * @template V
 *
 * @template-implements SeekableIterator<int, O>
 * @template-implements ArrayAccess<O, V>
 */
class SplObjectStorage implements Countable, SeekableIterator, Serializable, ArrayAccess
{
    /**
     * @param O $object
     * @param V $info
     */
    #[TentativeType]
    public function attach(object $object, mixed $info = null): void {}

    /**
     * @param O $object
     *
     * @return void
     */
    #[TentativeType]
    public function detach(object $object): void {}

    /**
     * @param O $object
     */
    #[TentativeType]
    public function contains(object $object): bool {}

    /**
     * @param SplObjectStorage<O, V> $storage
     */
    #[TentativeType]
    public function addAll(SplObjectStorage $storage): int {}

    /**
     * @param SplObjectStorage<O, V> $storage
     */
    #[TentativeType]
    public function removeAll(SplObjectStorage $storage): int {}

    /**
     * @param SplObjectStorage<O, V> $storage
     */
    #[TentativeType]
    public function removeAllExcept(SplObjectStorage $storage): int {}

    /**
     * @return V
     */
    #[TentativeType]
    public function getInfo(): mixed {}

    /**
     * @param V $info
     */
    #[TentativeType]
    public function setInfo(mixed $info): void {}

    /**
     * @param int $mode
     *
     * @return int
     */
    #[TentativeType]
    public function count(int $mode = COUNT_NORMAL): int {}

    /**
     * @return void
     */
    #[TentativeType]
    public function rewind(): void {}

    /**
     * @return bool
     */
    #[TentativeType]
    public function valid(): bool {}

    /**
     * @return int
     */
    #[TentativeType]
    public function key(): int {}

    /**
     * @return O
     */
    #[TentativeType]
    public function current(): object {}

    /**
     * @return void
     */
    #[TentativeType]
    public function next(): void {}

    #[TentativeType]
    public function unserialize(string $data): void {}

    #[TentativeType]
    public function serialize(): string {}

    /**
     * @param O $object
     */
    #[TentativeType]
    public function offsetExists($object): bool {}

    /**
     * @param O $object
     * @param V $info
     *
     * @return void
     */
    #[TentativeType]
    public function offsetSet(mixed $object, mixed $info = null): void {}

    /**
     * @param O $object
     */
    #[TentativeType]
    public function offsetUnset($object): void {}

    /**
     * @param O $object
     *
     * @return V
     */
    #[TentativeType]
    public function offsetGet($object): mixed {}

    /**
     * @param O $object
     */
    #[TentativeType]
    public function getHash(object $object): string {}

    #[TentativeType]
    public function __serialize(): array {}

    #[TentativeType]
    public function __unserialize(array $data): void {}

    #[TentativeType]
    public function __debugInfo(): array {}

    public function seek(int $offset): void {}
}

class MultipleIterator implements Iterator
{
    public const MIT_NEED_ANY = 0;
    public const MIT_NEED_ALL = 1;
    public const MIT_KEYS_NUMERIC = 0;
    public const MIT_KEYS_ASSOC = 2;

    public function __construct(int $flags = MultipleIterator::MIT_NEED_ALL | MultipleIterator::MIT_KEYS_NUMERIC) {}

    #[TentativeType]
    public function getFlags(): int {}

    #[TentativeType]
    public function setFlags(int $flags): void {}

    #[TentativeType]
    public function attachIterator(Iterator $iterator, int|string|null $info = null): void {}

    #[TentativeType]
    public function detachIterator(Iterator $iterator): void {}

    #[TentativeType]
    public function containsIterator(Iterator $iterator): bool {}

    #[TentativeType]
    public function countIterators(): int {}

    #[TentativeType]
    public function rewind(): void {}

    #[TentativeType]
    public function valid(): bool {}

    #[TentativeType]
    public function key(): array {}

    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    #[TentativeType]
    public function current(): array {}

    #[TentativeType]
    public function next(): void {}

    #[TentativeType]
    public function __debugInfo(): array {}
}

/**
 * @return list<class-string>
 */
function spl_classes(): array {}

/**
 * @param class-string $class
 */
function spl_autoload(string $class, ?string $file_extensions = null): void {}

function spl_autoload_extensions(?string $file_extensions = null): string {}

/**
 * @param null|(callable(class-string): void) $callback
 *
 * @throws TypeError
 */
function spl_autoload_register(?callable $callback = null, bool $throw = true, bool $prepend = false): bool {}

/**
 * @param null|(callable(class-string): void) $callback
 */
function spl_autoload_unregister(callable $callback): bool {}

/**
 * @return list<(callable(class-string): void)>
 */
function spl_autoload_functions(): array {}

/**
 * @param class-string $class
 */
function spl_autoload_call(string $class): void {}

/**
 * @pure
 */
function spl_object_hash(object $object): string {}

/**
 * @pure
 */
function spl_object_id(object $object): int {}

/**
 * @template K
 * @template V
 *
 * @param Traversable<K, V>|array<K, V> $iterator <p>
 *
 * @return ($preserve_keys is true ? array<K, V> : list<V>)
 */
function iterator_to_array(Traversable|array $iterator, bool $preserve_keys = true): array {}

/**
 * @return int<0, max>
 */
function iterator_count(Traversable|array $iterator): int {}

function iterator_apply(Traversable $iterator, callable $callback, ?array $args = null): int {}

/**
 * @param object|class-string $object_or_class
 *
 * @return array<class-string, class-string>|false
 *
 * @pure
 */
function class_parents($object_or_class, bool $autoload = true): array|false {}

/**
 * @param object|class-string $object_or_class
 *
 * @return array<trait-string, trait-string>|false
 *
 * @pure
 */
function class_uses($object_or_class, bool $autoload = true): array|false {}

/**
 * @param object|class-string $object_or_class
 *
 * @return ($object_or_class is object ? array<interface-string, interface-string> : array<interface-string, interface-string>|false)
 *
 * @pure
 */
function class_implements($object_or_class, bool $autoload = true): array|false {}
