<?php

class ReflectionException extends Exception {}

interface Reflector extends Stringable
{
}

class Reflection
{
    /**
     * @return list<string>
     */
    #[TentativeType]
    public static function getModifierNames(int $modifiers): array {}
}

/**
 * @template T of Fiber
 */
#[Mago\AvailableSince(80100)]
final class ReflectionFiber
{
    /**
     * @param T $fiber
     */
    public function __construct(Fiber $fiber) {}

    /**
     * @return T
     *
     * @pure
     */
    public function getFiber(): Fiber {}

    /**
     * @pure
     */
    public function getExecutingFile(): ?string {}

    /**
     * @pure
     */
    public function getExecutingLine(): ?int {}

    /**
     * @pure
     */
    public function getCallable(): callable {}

    /**
     * @return list<array<string, mixed>>
     *
     * @pure
     */
    public function getTrace(int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT): array {}
}

final class ReflectionGenerator
{
    public function __construct(Generator $generator) {}

    /**
     * @pure
     */
    public function getExecutingLine(): int {}

    /**
     * @pure
     */
    public function getExecutingFile(): string {}

    /**
     * @return list<array<string, mixed>>
     *
     * @pure
     */
    public function getTrace(int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT): array {}

    /**
     * @pure
     */
    public function getFunction(): ReflectionFunctionAbstract {}

    /**
     * @pure
     */
    public function getThis(): ?object {}

    /**
     * @pure
     */
    public function getExecutingGenerator(): Generator {}

    /**
     * @pure
     */
    public function isClosed(): bool {}
}

class ReflectionExtension implements Reflector
{
    /**
     * @readonly
     */
    public string $name;

    /**
     * @throws ReflectionException
     */
    public function __construct(string $name) {}

    public function __toString(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getName(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getVersion(): ?string {}

    /**
     * @return array<string, ReflectionFunction>
     *
     * @pure
     */
    #[TentativeType]
    public function getFunctions(): array {}

    /**
     * @return array<string, mixed>
     *
     * @pure
     */
    #[TentativeType]
    public function getConstants(): array {}

    /**
     * @return array<string, mixed>
     *
     * @pure
     */
    #[TentativeType]
    public function getINIEntries(): array {}

    /**
     * @return array<class-string, ReflectionClass>
     *
     * @pure
     */
    #[TentativeType]
    public function getClasses(): array {}

    /**
     * @return list<class-string>
     *
     * @pure
     */
    #[TentativeType]
    public function getClassNames(): array {}

    /**
     * @return array<string, string>
     *
     * @pure
     */
    #[TentativeType]
    public function getDependencies(): array {}

    #[TentativeType]
    public function info(): void {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isPersistent(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isTemporary(): bool {}

    private function __clone(): void {}
}

abstract class ReflectionType implements Stringable
{
    /**
     * @pure
     */
    #[TentativeType]
    public function allowsNull(): bool {}

    public function __toString(): string {}

    private function __clone(): void {}
}

class ReflectionNamedType extends ReflectionType
{
    /**
     * @pure
     */
    #[TentativeType]
    public function getName(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isBuiltin(): bool {}
}

#[Mago\AvailableSince(80100)]
class ReflectionIntersectionType extends ReflectionType
{
    /**
     * @return list<ReflectionType>
     *
     * @pure
     */
    public function getTypes(): array {}
}

class ReflectionUnionType extends ReflectionType
{
    /**
     * @return list<ReflectionNamedType|ReflectionIntersectionType>
     *
     * @pure
     */
    public function getTypes(): array {}
}

class ReflectionZendExtension implements Reflector
{
    /**
     * @readonly
     */
    public string $name;

    /**
     * @throws ReflectionException
     */
    public function __construct(string $name) {}

    public function __toString(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getName(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getVersion(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getAuthor(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getURL(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getCopyright(): string {}

    private function __clone(): void {}
}

/**
 * @template T of object
 */
class ReflectionAttribute implements Reflector
{
    /**
     * @var class-string<T>
     *
     * @readonly
     */
    public string $name;

    public const IS_INSTANCEOF = 2;

    private function __construct() {}

    public function __toString(): string {}

    /**
     * @return class-string<T>
     *
     * @pure
     */
    public function getName(): string {}

    /**
     * @pure
     */
    public function getTarget(): int {}

    /**
     * @pure
     */
    public function isRepeated(): bool {}

    /**
     * @pure
     */
    public function getArguments(): array {}

    /**
     * @return T
     */
    public function newInstance(): object {}

    private function __clone(): void {}

}

/**
 * @template T of object = object
 */
class ReflectionClassConstant implements Reflector
{
    /**
     * @readonly
     */
    public string $name;

    /**
     * @var class-string<T>
     *
     * @readonly
     */
    public string $class;

    /**
     * @readonly
     */
    public bool $isFinal;

    public const IS_PUBLIC = 1;

    public const IS_PROTECTED = 2;

    public const IS_PRIVATE = 4;

    public const IS_FINAL = 5;

    /**
     * @param class-string<T>|T $class
     *
     * @throws ReflectionException
     */
    public function __construct(string|object $class, string $constant) {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getDeclaringClass(): ReflectionClass {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getDocComment(): string|false {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getModifiers(): int {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getName(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getValue(): mixed {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isPrivate(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isProtected(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isPublic(): bool {}

    public function __toString(): string {}

    /**
     * @template A
     *
     * @param class-string<A>|null $name
     * @param int $flags
     *
     * @return list<ReflectionAttribute<A>>
     *
     * @pure
     */
    public function getAttributes(?string $name = null, int $flags = 0): array {}

    private function __clone(): void {}

    /**
     * @pure
     */
    #[Mago\AvailableSince(80100)]
    public function isEnumCase(): bool {}

    /**
     * @pure
     */
    #[Mago\AvailableSince(80100)]
    public function isFinal(): bool {}

    /**
     * @pure
     */
    #[Mago\AvailableSince(80300)]
    public function hasType(): bool {}

    /**
     * @pure
     */
    #[Mago\AvailableSince(80300)]
    public function getType(): ?ReflectionType {}

    /**
     * @pure
     */
    #[Mago\AvailableSince(80400)]
    public function isDeprecated(): bool {}
}

#[Mago\AvailableSince(80400)]
enum PropertyHookType: string
{
    case Get = 'get';
    case Set = 'set';
}

/**
 * @template T of object = object
 */
class ReflectionProperty implements Reflector
{
    public const IS_ABSTRACT = 64;

    public const IS_VIRTUAL = 512;

    public const IS_STATIC = 16;

    public const IS_PUBLIC = 1;

    public const IS_PROTECTED = 2;

    public const IS_PRIVATE = 4;

    public const IS_READONLY = 128;

    public const IS_PROTECTED_SET = 2048;

    public const IS_PRIVATE_SET = 4096;

    public const IS_FINAL = 32;

    /**
     * @readonly
     */
    public string $name;

    /**
     * @var class-string<T>
     *
     * @readonly
     */
    public string $class;

    /**
     * @param class-string<T>|T $class
     *
     * @throws ReflectionException
     */
    public function __construct(object|string $class, string $property) {}

    public function __toString(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getName(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getValue(?object $object = null): mixed {}

    #[TentativeType]
    public function setValue(mixed $objectOrValue, mixed $value = UNKNOWN): void {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isPublic(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isPrivate(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isProtected(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isStatic(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isDefault(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getModifiers(): int {}

    /**
     * @return ReflectionClass<T>
     *
     * @pure
     */
    #[TentativeType]
    public function getDeclaringClass(): ReflectionClass {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getDocComment(): string|false {}

    /**
     * @pure
     */
    #[TentativeType]
    #[Deprecated(since: '8.5', message: 'as it has no effect')]
    public function setAccessible(bool $accessible): void {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getType(): ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null {}

    /**
     * @pure
     */
    #[TentativeType]
    public function hasType(): bool {}

    /**
     * @param ?T $object
     *
     * @pure
     */
    #[TentativeType]
    public function isInitialized(?object $object = null): bool {}

    /**
     * @pure
     */
    public function isPromoted(): bool {}

    private function __clone(): void {}

    public function hasDefaultValue(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getDefaultValue(): mixed {}

    /**
     * @template A
     *
     * @param class-string<A>|null $name
     * @param int $flags
     *
     * @return list<ReflectionAttribute<A>>
     *
     * @pure
     */
    public function getAttributes(?string $name = null, int $flags = 0): array {}

    #[Mago\AvailableSince(80100)]
    public function isReadOnly(): bool {}

    #[Mago\AvailableSince(80400)]
    public function getRawValue(object $object): mixed {}

    #[Mago\AvailableSince(80400)]
    public function setRawValue(object $object, mixed $value): void {}

    #[Mago\AvailableSince(80400)]
    public function isAbstract(): bool {}

    #[Mago\AvailableSince(80400)]
    public function isVirtual(): bool {}

    #[Mago\AvailableSince(80400)]
    public function getSettableType(): ?ReflectionType {}

    #[Mago\AvailableSince(80400)]
    public function hasHooks(): bool {}

    /**
     * @return array<string, ReflectionMethod>
     */
    #[Mago\AvailableSince(80400)]
    public function getHooks(): array {}

    #[Mago\AvailableSince(80400)]
    public function hasHook(PropertyHookType $type): bool {}

    #[Mago\AvailableSince(80400)]
    public function getHook(PropertyHookType $type): ?ReflectionMethod {}

    #[Mago\AvailableSince(80400)]
    public function isPrivateSet(): bool {}

    #[Mago\AvailableSince(80400)]
    public function isProtectedSet(): bool {}

    /**
     * @param T $object
     */
    #[Mago\AvailableSince(80400)]
    public function setRawValueWithoutLazyInitialization(object $object, mixed $value): void {}

    /**
     * @param T $object
     */
    #[Mago\AvailableSince(80400)]
    public function skipLazyInitialization(object $object): void {}

    public function isDynamic(): bool {}

    public function isFinal(): bool {}

    /**
     * @param T $object
     */
    public function isLazy(object $object): bool {}

    /**
     * @param T $object
     */
    #[Mago\AvailableSince(80600)]
    public function isReadable(?object $object = null): bool {}

    /**
     * @param T $object
     */
    #[Mago\AvailableSince(80600)]
    public function isWriteable(?object $object = null): bool {}
}

final class ReflectionReference
{
    private function __construct() {}

    public static function fromArrayElement(array $array, string|int $key): ?ReflectionReference {}

    /**
     * @pure
     */
    public function getId(): string {}

    private function __clone(): void {}
}

#[Mago\AvailableSince(80500)]
final class ReflectionConstant implements Reflector
{
    /**
     * @readonly
     */
    public string $name;

    public function __construct(string $name) {}

    public function getName(): string {}

    public function getNamespaceName(): string {}

    public function getShortName(): string {}

    public function getValue(): mixed {}

    public function isDeprecated(): bool {}

    public function __toString(): string {}
}

class ReflectionParameter implements Reflector
{
    /**
     * @readonly
     */
    public string $name;

    /**
     * @param string|array{0: class-string, 1: string}|object $function
     *
     * @throws ReflectionException
     */
    public function __construct(string|array|object $function, string|int $param) {}

    public function __toString(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getName(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isPassedByReference(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function canBePassedByValue(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getDeclaringFunction(): ReflectionFunctionAbstract {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getDeclaringClass(): ?ReflectionClass {}

    /**
     * @pure
     */
    #[TentativeType]
    #[Deprecated(
        since: '8.0',
        reason: 'Use ReflectionParameter::getType() and the ReflectionType APIs should be used instead.',
    )]
    public function getClass(): ?ReflectionClass {}

    /**
     * @pure
     */
    #[TentativeType]
    public function hasType(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getType(): ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null {}

    /**
     * @pure
     */
    #[TentativeType]
    #[Deprecated(
        since: '8.0',
        reason: 'Use ReflectionParameter::getType() and the ReflectionType APIs should be used instead.',
    )]
    public function isArray(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    #[Deprecated(
        since: '8.0',
        reason: 'Use ReflectionParameter::getType() and the ReflectionType APIs should be used instead.',
    )]
    public function isCallable(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function allowsNull(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getPosition(): int {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isOptional(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isDefaultValueAvailable(): bool {}

    /**
     * @throws ReflectionException
     *
     * @pure
     */
    #[TentativeType]
    public function getDefaultValue(): mixed {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isDefaultValueConstant(): bool {}

    /**
     * @throws ReflectionException
     *
     * @pure
     */
    #[TentativeType]
    public function getDefaultValueConstantName(): ?string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isVariadic(): bool {}

    /**
     * @pure
     */
    public function isPromoted(): bool {}

    /**
     * @template A
     *
     * @param class-string<A>|null $name
     * @param int $flags
     *
     * @return list<ReflectionAttribute<A>>
     *
     * @pure
     */
    public function getAttributes(?string $name = null, int $flags = 0): array {}

    private function __clone(): void {}
}

abstract class ReflectionFunctionAbstract implements Reflector
{
    /**
     * @readonly
     */
    public string $name;

    private function __clone(): void {}

    #[TentativeType]
    public function inNamespace(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isClosure(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isDeprecated(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isInternal(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isUserDefined(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isGenerator(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isVariadic(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getClosureThis(): ?object {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getClosureScopeClass(): ?ReflectionClass {}

    /**
     * @pure
     */
    #[TentativeType]
    #[Mago\AvailableSince(80100)]
    public function getClosureCalledClass(): ?ReflectionClass {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getDocComment(): string|false {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getEndLine(): int|false {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getExtension(): ?ReflectionExtension {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getExtensionName(): string|false {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getFileName(): string|false {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getName(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getNamespaceName(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getNumberOfParameters(): int {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getNumberOfRequiredParameters(): int {}

    /**
     * @return ReflectionParameter[]
     *
     * @pure
     */
    #[TentativeType]
    public function getParameters(): array {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getReturnType(): ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getShortName(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getStartLine(): int|false {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getStaticVariables(): array {}

    /**
     * @pure
     */
    #[TentativeType]
    public function returnsReference(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function hasReturnType(): bool {}

    /**
     * @template A
     *
     * @param class-string<A>|null $name
     *
     * @return list<ReflectionAttribute<A>>
     *
     * @pure
     */
    public function getAttributes(?string $name = null, int $flags = 0): array {}

    /**
     * @pure
     */
    #[Mago\AvailableSince(80100)]
    public function getClosureUsedVariables(): array {}

    /**
     * @pure
     */
    #[Mago\AvailableSince(80100)]
    public function hasTentativeReturnType(): bool {}

    /**
     * @pure
     */
    #[Mago\AvailableSince(80100)]
    public function getTentativeReturnType(): ?ReflectionType {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isStatic(): bool {}

    public function __toString(): string {}
}

class ReflectionFunction extends ReflectionFunctionAbstract
{
    /**
     * @readonly
     */
    public string $name;

    public const IS_DEPRECATED = 2048;

    /**
     * @throws ReflectionException
     */
    public function __construct(Closure|string $function) {}

    public function __toString(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    #[\Deprecated(since: '8.0', reason: 'ReflectionFunction can no longer be constructed for disabled functions.')]
    public function isDisabled(): bool {}

    #[TentativeType]
    public function invoke(mixed ...$args): mixed {}

    #[TentativeType]
    public function invokeArgs(array $args): mixed {}

    /**
     * @return Closure|null
     *
     * @pure
     */
    #[TentativeType]
    public function getClosure(): Closure {}

    /**
     * @pure
     */
    public function isAnonymous(): bool {}
}

/**
 * @template T of object = object
 */
class ReflectionMethod extends ReflectionFunctionAbstract
{
    /**
     * @readonly
     */
    public string $name;

    /**
     * @var class-string<T>
     *
     * @readonly
     */
    public string $class;

    public const IS_STATIC = 16;

    public const IS_PUBLIC = 1;

    public const IS_PROTECTED = 2;

    public const IS_PRIVATE = 4;

    public const IS_ABSTRACT = 64;

    public const IS_FINAL = 32;

    /**
     * @param class-string<T>|T $objectOrMethod
     *
     * @throws ReflectionException
     */
    public function __construct(object|string $objectOrMethod, ?string $method = null) {}

    public function __toString(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isPublic(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isPrivate(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isProtected(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isAbstract(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isFinal(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isStatic(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isConstructor(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isDestructor(): bool {}

    /**
     * @param T|null $object
     *
     * @throws ValueError
     * @throws ReflectionException
     */
    #[TentativeType]
    public function getClosure(?object $object = null): Closure {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getModifiers(): int {}

    /**
     * @param T|null $object
     *
     * @throws ReflectionException
     */
    public function invoke($object, ...$args) {}

    /**
     * @param T|null $object
     *
     * @throws ReflectionException
     */
    #[TentativeType]
    public function invokeArgs(?object $object, array $args): mixed {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getDeclaringClass(): ReflectionClass {}

    /**
     * @throws ReflectionException
     *
     * @pure
     */
    #[TentativeType]
    public function getPrototype(): ReflectionMethod {}

    /**
     * @pure
     */
    #[TentativeType]
    #[Deprecated(since: '8.5', message: 'as it has no effect')]
    public function setAccessible(bool $accessible): void {}

    /**
     * @pure
     */
    #[Mago\AvailableSince(80100)]
    public function hasPrototype(): bool {}

    #[Mago\AvailableSince(80300)]
    public static function createFromMethodName(string $method): static {}
}

/**
 * @template T of object = object
 */
class ReflectionClass implements Reflector
{
    /**
     * @var class-string<T>
     */
    public string $name;

    public const IS_IMPLICIT_ABSTRACT = 16;

    public const IS_EXPLICIT_ABSTRACT = 64;

    public const IS_FINAL = 32;

    public const IS_READONLY = 65536;

    public const int SKIP_INITIALIZATION_ON_SERIALIZE = 0;

    public const int SKIP_DESTRUCTOR = 0;

    /**
     * @param class-string<T>|T $objectOrClass
     *
     * @throws ReflectionException
     */
    public function __construct(object|string $objectOrClass) {}

    public function __toString(): string {}

    /**
     * @pure
     *
     * @return class-string<T>
     */
    #[TentativeType]
    public function getName(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isInternal(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isUserDefined(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isInstantiable(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isCloneable(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getFileName(): string|false {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getStartLine(): int|false {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getEndLine(): int|false {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getDocComment(): string|false {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getConstructor(): ?ReflectionMethod {}

    /**
     * @pure
     */
    #[TentativeType]
    public function hasMethod(string $name): bool {}

    /**
     * @throws ReflectionException
     *
     * @return ReflectionMethod<T>
     *
     * @pure
     */
    #[TentativeType]
    public function getMethod(string $name): ReflectionMethod {}

    /**
     * @return list<ReflectionMethod<T>>
     *
     * @pure
     */
    #[TentativeType]
    public function getMethods(?int $filter = null): array {}

    /**
     * @pure
     */
    #[TentativeType]
    public function hasProperty(string $name): bool {}

    /**
     * @return ReflectionProperty<T>
     *
     * @throws ReflectionException
     *
     * @pure
     */
    #[TentativeType]
    public function getProperty(string $name): ReflectionProperty {}

    /**
     * @return list<ReflectionProperty<T>>
     *
     * @pure
     */
    #[TentativeType]
    public function getProperties(?int $filter = null): array {}

    /**
     * @return false|ReflectionClassConstant<T>
     *
     * @pure
     */
    #[TentativeType]
    public function getReflectionConstant(string $name): ReflectionClassConstant|false {}

    /**
     * @return list<ReflectionClassConstant<T>>
     *
     * @pure
     */
    #[TentativeType]
    public function getReflectionConstants(?int $filter = null): array {}

    /**
     * @pure
     */
    #[TentativeType]
    public function hasConstant(string $name): bool {}

    /**
     * @return array<string, mixed>
     *
     * @pure
     */
    #[TentativeType]
    public function getConstants(?int $filter = null): array {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getConstant(string $name): mixed {}

    /**
     * @return array<interface-string, ReflectionClass>
     *
     * @pure
     */
    #[TentativeType]
    public function getInterfaces(): array {}

    /**
     * @return list<interface-string>
     */
    #[TentativeType]
    public function getInterfaceNames(): array {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isAnonymous(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isInterface(): bool {}

    /**
     * @return array<trait-string, ReflectionClass>
     *
     * @pure
     */
    #[TentativeType]
    public function getTraits(): array {}

    /**
     * @return list<trait-string>
     *
     * @pure
     */
    #[TentativeType]
    public function getTraitNames(): array {}

    /**
     * @return array<string, string>
     *
     * @pure
     */
    #[TentativeType]
    public function getTraitAliases(): array {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isTrait(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isAbstract(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isFinal(): bool {}

    /**
     * @pure
     */
    #[Mago\AvailableSince(80200)]
    public function isReadOnly(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getModifiers(): int {}

    /**
     * @pure
     *
     * @assert-if-true T $object
     */
    #[TentativeType]
    public function isInstance(object $object): bool {}

    /**
     * @return T
     *
     * @throws ReflectionException
     */
    public function newInstance(...$args) {}

    /**
     * @return T
     *
     * @throws ReflectionException
     */
    #[TentativeType]
    public function newInstanceWithoutConstructor(): object {}

    /**
     * @return T|null
     *
     * @throws ReflectionException
     */
    #[TentativeType]
    public function newInstanceArgs(array $args = []): ?object {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getParentClass(): ReflectionClass|false {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isSubclassOf(ReflectionClass|string $class): bool {}

    /**
     * @pure
     * @return array<string, mixed>
     */
    #[TentativeType]
    public function getStaticProperties(): array {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getStaticPropertyValue(string $name, mixed $default = UNKNOWN): mixed {}

    #[TentativeType]
    public function setStaticPropertyValue(string $name, mixed $value): void {}

    /**
     * @pure
     * @return array<string, mixed>
     */
    #[TentativeType]
    public function getDefaultProperties(): array {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isIterateable(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function isIterable(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function implementsInterface(ReflectionClass|string $interface): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getExtension(): ?ReflectionExtension {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getExtensionName(): string|false {}

    /**
     * @pure
     */
    #[TentativeType]
    public function inNamespace(): bool {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getNamespaceName(): string {}

    /**
     * @pure
     */
    #[TentativeType]
    public function getShortName(): string {}

    /**
     * @template A
     *
     * @param class-string<A>|null $name
     *
     * @return list<ReflectionAttribute<A>>
     *
     * @pure
     */
    public function getAttributes(?string $name = null, int $flags = 0): array {}

    private function __clone(): void {}

    /**
     * @pure
     */
    #[Mago\AvailableSince(80100)]
    public function isEnum(): bool {}

    #[Mago\AvailableSince(80400)]
    public function newLazyGhost(callable $initializer, int $options = 0): object {}

    /**
     * @return T
     */
    #[Mago\AvailableSince(80400)]
    public function newLazyProxy(callable $factory, int $options = 0): object {}

    /**
     * @param T $object
     */
    #[Mago\AvailableSince(80400)]
    public function resetAsLazyGhost(object $object, callable $initializer, int $options = 0): void {}

    /**
     * @param T $object
     */
    #[Mago\AvailableSince(80400)]
    public function resetAsLazyProxy(object $object, callable $factory, int $options = 0): void {}

    /**
     * @param T $object
     */
    #[Mago\AvailableSince(80400)]
    public function initializeLazyObject(object $object): object {}

    /**
     * @param T $object
     *
     * @pure
     */
    #[Mago\AvailableSince(80400)]
    public function isUninitializedLazyObject(object $object): bool {}

    /**
     * @param T $object
     */
    #[Mago\AvailableSince(80400)]
    public function markLazyObjectAsInitialized(object $object): object {}

    /**
     * @param T $object
     */
    #[Mago\AvailableSince(80400)]
    public function getLazyInitializer(object $object): ?callable {}
}

/**
 * @template T of object = object
 *
 * @extends ReflectionClass<T>
 */
class ReflectionObject extends ReflectionClass
{
    /**
     * @param T $object
     */
    public function __construct(object $object) {}
}

/**
 * @extends ReflectionClass<UnitEnum|BackedEnum>
 */
#[Mago\AvailableSince(80100)]
class ReflectionEnum extends ReflectionClass
{
    public function hasCase(string $name): bool {}

    /**
     * @return list<ReflectionEnumUnitCase|ReflectionEnumBackedCase>
     */
    public function getCases(): array {}

    /**
     * @throws ReflectionException
     */
    public function getCase(string $name): static {}

    /**
     * @pure
     */
    public function isBacked(): bool {}

    /**
     * @pure
     */
    public function getBackingType(): ?ReflectionNamedType {}
}

/**
 * @extends ReflectionClassConstant<UnitEnum>
 */
#[Mago\AvailableSince(80100)]
class ReflectionEnumUnitCase extends ReflectionClassConstant
{
    /**
     * @pure
     */
    public function getValue(): UnitEnum {}

    /**
     * @pure
     */
    public function getEnum(): ReflectionEnum {}
}

/**
 * @extends ReflectionEnumUnitCase<BackedEnum>
 */
#[Mago\AvailableSince(80100)]
class ReflectionEnumBackedCase extends ReflectionEnumUnitCase
{
    /**
     * @pure
     */
    public function getBackingValue(): int|string {}
}
