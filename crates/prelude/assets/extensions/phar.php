<?php

class PharException extends Exception {}

class Phar extends RecursiveDirectoryIterator implements RecursiveIterator, SeekableIterator, Countable, ArrayAccess
{
    public const BZ2 = 8192;
    public const GZ = 4096;
    public const NONE = 0;
    public const PHAR = 1;
    public const TAR = 2;
    public const ZIP = 3;
    public const COMPRESSED = 61440;
    public const PHP = 0;
    public const PHPS = 1;
    public const MD5 = 1;
    public const OPENSSL = 16;
    public const SHA1 = 2;
    public const SHA256 = 3;
    public const SHA512 = 4;
    public const OPENSSL_SHA256 = 5;
    public const OPENSSL_SHA512 = 6;

    /**
     * @throws BadMethodCallException
     * @throws UnexpectedValueException
     */
    public function __construct(
        string $filename,
        int $flags = FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS,
        ?string $alias = null,
    ) {}

    public function __destruct() {}

    #[TentativeType]
    public function addEmptyDir(string $directory): void {}

    #[TentativeType]
    public function addFile(string $filename, ?string $localName = null): void {}

    #[TentativeType]
    public function addFromString(string $localName, string $contents): void {}

    #[TentativeType]
    public function buildFromDirectory(string $directory, string $pattern = ''): array {}

    #[TentativeType]
    public function buildFromIterator(Traversable $iterator, ?string $baseDirectory = null): array {}

    #[TentativeType]
    public function compressFiles(int $compression): void {}

    #[TentativeType]
    public function decompressFiles(): bool {}

    #[TentativeType]
    public function compress(int $compression, ?string $extension = null): ?Phar {}

    #[TentativeType]
    public function decompress(?string $extension = null): ?Phar {}

    #[TentativeType]
    public function convertToExecutable(
        ?int $format = null,
        ?int $compression = null,
        ?string $extension = null,
    ): ?Phar {}

    #[TentativeType]
    public function convertToData(
        ?int $format = null,
        ?int $compression = null,
        ?string $extension = null,
    ): ?PharData {}

    #[TentativeType]
    public function copy(string $to, string $from): bool {}

    /**
     * @return int<0, max>
     */
    #[TentativeType]
    public function count(int $mode = COUNT_NORMAL): int {}

    #[TentativeType]
    public function delete(string $localName): bool {}

    #[TentativeType]
    public function delMetadata(): bool {}

    #[TentativeType]
    public function extractTo(string $directory, array|string|null $files = null, bool $overwrite = false): bool {}

    #[TentativeType]
    public function getAlias(): ?string {}

    #[TentativeType]
    public function getMetadata(array $unserializeOptions = []): mixed {}

    #[TentativeType]
    public function getModified(): bool {}

    /**
     * @return false|array{
     *   hash: string,
     *   hash_type: string
     * }
     */
    #[TentativeType]
    public function getSignature(): array|false {}

    #[TentativeType]
    public function getStub(): string {}

    #[TentativeType]
    public function getVersion(): string {}

    #[TentativeType]
    public function hasMetadata(): bool {}

    #[TentativeType]
    public function isBuffering(): bool {}

    #[TentativeType]
    public function isCompressed(): int|false {}

    #[TentativeType]
    public function isFileFormat(int $format): bool {}

    #[TentativeType]
    public function isWritable(): bool {}

    /**
     * @param string $localName
     */
    #[TentativeType]
    public function offsetExists($localName): bool {}

    /**
     * @param string $localName
     */
    #[TentativeType]
    public function offsetGet($localName): SplFileInfo {}

    /**
     * @param string $localName
     * @param string $value
     */
    #[TentativeType]
    public function offsetSet($localName, $value): void {}

    /**
     * @param string $localName
     */
    #[TentativeType]
    public function offsetUnset($localName): void {}

    #[TentativeType]
    public function setAlias(string $alias): bool {}

    #[TentativeType]
    public function setDefaultStub(?string $index = null, ?string $webIndex = null): bool {}

    #[TentativeType]
    public function setMetadata(mixed $metadata): void {}

    #[TentativeType]
    public function setSignatureAlgorithm(int $algo, ?string $privateKey = null): void {}

    /**
     * @param string $stub
     */
    #[TentativeType]
    public function setStub($stub, int $length = UNKNOWN): bool {}

    #[TentativeType]
    public function startBuffering(): void {}

    #[TentativeType]
    public function stopBuffering(): void {}

    final public static function apiVersion(): string {}

    final public static function canCompress(int $compression = 0): bool {}

    final public static function canWrite(): bool {}

    final public static function createDefaultStub(?string $index = null, ?string $webIndex = null): string {}

    /**
     * @return list<non-empty-string>
     */
    final public static function getSupportedCompression(): array {}

    /**
     * @return list<non-empty-string>
     */
    final public static function getSupportedSignatures(): array {}

    final public static function interceptFileFuncs(): void {}

    final public static function isValidPharFilename(string $filename, bool $executable = true): bool {}

    final public static function loadPhar(string $filename, ?string $alias = null): bool {}

    final public static function mapPhar(?string $alias = null, int $offset = 0): bool {}

    final public static function running(bool $returnPhar = true): string {}

    final public static function mount(string $pharPath, string $externalPath): void {}

    final public static function mungServer(array $variables): void {}

    /**
     * @throws PharException
     */
    final public static function unlinkArchive(string $filename): bool {}

    final public static function webPhar(
        ?string $alias = null,
        ?string $index = null,
        ?string $fileNotFoundScript = null,
        array $mimeTypes = [],
        ?callable $rewrite = null,
    ): void {}

    /**
     * @param bool $allow_links
     *
     * @return bool
     */
    public function hasChildren($allow_links = false) {}

    public function getChildren() {}

    /**
     * @return void
     */
    public function rewind() {}

    /**
     * @return void
     */
    public function next() {}

    /**
     * @return string
     */
    public function key() {}

    public function current() {}

    /**
     * @return bool
     */
    public function valid() {}

    /**
     * @param int $position
     *
     * @return void
     */
    public function seek($position) {}

    public function _bad_state_ex() {}
}

class PharData extends Phar
{
    public function __construct(
        string $filename,
        int $flags = FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS,
        ?string $alias = null,
        int $format = 0,
    ) {}

    /**
     * @param string $localName
     */
    #[TentativeType]
    public function offsetExists($localName): bool {}

    /**
     * @param string $localName
     */
    #[TentativeType]
    public function offsetGet($localName): SplFileInfo {}

    /**
     * @param string $localName
     * @param string $value
     */
    #[TentativeType]
    public function offsetSet($localName, $value): void {}

    /**
     * @param string $localName
     */
    #[TentativeType]
    public function offsetUnset($localName): void {}

    /**
     * @param bool $allow_links
     *
     * @return bool
     */
    public function hasChildren($allow_links = false) {}

    public function getChildren() {}

    public function rewind() {}

    public function next() {}

    /**
     * @return string
     */
    public function key() {}

    public function current() {}

    /**
     * @return bool
     */
    public function valid() {}

    /**
     * @param int $position
     *
     * @return void
     */
    public function seek($position) {}
}

class PharFileInfo extends SplFileInfo
{
    public function __construct(string $filename) {}

    public function __destruct() {}

    #[TentativeType]
    public function chmod(int $perms): void {}

    #[TentativeType]
    public function compress(int $compression): bool {}

    #[TentativeType]
    public function decompress(): bool {}

    #[TentativeType]
    public function delMetadata(): bool {}

    #[TentativeType]
    public function getCompressedSize(): int {}

    #[TentativeType]
    public function getCRC32(): int {}

    #[TentativeType]
    public function getContent(): string {}

    #[TentativeType]
    public function getMetadata(array $unserializeOptions = []): mixed {}

    #[TentativeType]
    public function getPharFlags(): int {}

    #[TentativeType]
    public function hasMetadata(): bool {}

    #[TentativeType]
    public function isCompressed(?int $compression = null): bool {}

    #[TentativeType]
    public function isCRCChecked(): bool {}

    #[TentativeType]
    public function setMetadata(mixed $metadata): void {}
}
