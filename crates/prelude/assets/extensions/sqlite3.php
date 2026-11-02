<?php

/**
 * @var int
 */
const SQLITE3_ASSOC = UNKNOWN;

/**
 * @var int
 */
const SQLITE3_NUM = UNKNOWN;

/**
 * @var int
 */
const SQLITE3_BOTH = UNKNOWN;

/**
 * @var int
 */
const SQLITE3_INTEGER = UNKNOWN;

/**
 * @var int
 */
const SQLITE3_FLOAT = UNKNOWN;

/**
 * @var int
 */
const SQLITE3_TEXT = UNKNOWN;

/**
 * @var int
 */
const SQLITE3_BLOB = UNKNOWN;

/**
 * @var int
 */
const SQLITE3_NULL = UNKNOWN;

/**
 * @var int
 */
const SQLITE3_OPEN_READONLY = UNKNOWN;

/**
 * @var int
 */
const SQLITE3_OPEN_READWRITE = UNKNOWN;

/**
 * @var int
 */
const SQLITE3_OPEN_CREATE = UNKNOWN;

/**
 * @var int
 */
const SQLITE3_DETERMINISTIC = UNKNOWN;

#[Mago\AvailableSince(80300)]
class SQLite3Exception extends \Exception {}

class SQLite3
{
    public const int OK = UNKNOWN;
    public const int DENY = UNKNOWN;
    public const int IGNORE = UNKNOWN;
    public const int CREATE_INDEX = UNKNOWN;
    public const int CREATE_TABLE = UNKNOWN;
    public const int CREATE_TEMP_INDEX = UNKNOWN;
    public const int CREATE_TEMP_TABLE = UNKNOWN;
    public const int CREATE_TEMP_TRIGGER = UNKNOWN;
    public const int CREATE_TEMP_VIEW = UNKNOWN;
    public const int CREATE_TRIGGER = UNKNOWN;
    public const int CREATE_VIEW = UNKNOWN;
    public const int DELETE = UNKNOWN;
    public const int DROP_INDEX = UNKNOWN;
    public const int DROP_TABLE = UNKNOWN;
    public const int DROP_TEMP_INDEX = UNKNOWN;
    public const int DROP_TEMP_TABLE = UNKNOWN;
    public const int DROP_TEMP_TRIGGER = UNKNOWN;
    public const int DROP_TEMP_VIEW = UNKNOWN;
    public const int DROP_TRIGGER = UNKNOWN;
    public const int DROP_VIEW = UNKNOWN;
    public const int INSERT = UNKNOWN;
    public const int PRAGMA = UNKNOWN;
    public const int READ = UNKNOWN;
    public const int SELECT = UNKNOWN;
    public const int TRANSACTION = UNKNOWN;
    public const int UPDATE = UNKNOWN;
    public const int ATTACH = UNKNOWN;
    public const int DETACH = UNKNOWN;
    public const int ALTER_TABLE = UNKNOWN;
    public const int REINDEX = UNKNOWN;
    public const int ANALYZE = UNKNOWN;
    public const int CREATE_VTABLE = UNKNOWN;
    public const int DROP_VTABLE = UNKNOWN;
    public const int FUNCTION = UNKNOWN;
    public const int SAVEPOINT = UNKNOWN;
    public const int COPY = UNKNOWN;
    public const int RECURSIVE = UNKNOWN;

    public function __construct(
        string $filename,
        int $flags = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE,
        string $encryptionKey = '',
    ) {}

    #[TentativeType]
    public function open(
        string $filename,
        int $flags = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE,
        string $encryptionKey = '',
    ): void {}

    #[TentativeType]
    public function close(): bool {}

    #[TentativeType]
    public static function version(): array {}

    #[TentativeType]
    public function lastInsertRowID(): int {}

    #[TentativeType]
    public function lastErrorCode(): int {}

    #[TentativeType]
    public function lastExtendedErrorCode(): int {}

    #[TentativeType]
    public function lastErrorMsg(): string {}

    #[TentativeType]
    public function changes(): int {}

    #[TentativeType]
    public function busyTimeout(int $milliseconds): bool {}

    #[TentativeType]
    public function loadExtension(string $name): bool {}

    #[TentativeType]
    public function backup(
        SQLite3 $destination,
        string $sourceDatabase = 'main',
        string $destinationDatabase = 'main',
    ): bool {}

    #[TentativeType]
    public static function escapeString(string $string): string {}

    #[TentativeType]
    public function prepare(string $query): SQLite3Stmt|false {}

    #[TentativeType]
    public function exec(string $query): bool {}

    #[TentativeType]
    public function query(string $query): SQLite3Result|false {}

    #[TentativeType]
    public function querySingle(string $query, bool $entireRow = false): mixed {}

    #[TentativeType]
    public function createFunction(string $name, callable $callback, int $argCount = -1, int $flags = 0): bool {}

    #[TentativeType]
    public function createAggregate(
        string $name,
        callable $stepCallback,
        callable $finalCallback,
        int $argCount = -1,
    ): bool {}

    #[TentativeType]
    public function createCollation(string $name, callable $callback): bool {}

    /** @return resource|false */
    public function openBlob(
        string $table,
        string $column,
        int $rowid,
        string $database = 'main',
        int $flags = SQLITE3_OPEN_READONLY,
    ) {}

    #[TentativeType]
    public function enableExceptions(bool $enable = false): bool {}

    #[TentativeType]
    public function enableExtendedResultCodes(bool $enable = true): bool {}

    #[TentativeType]
    public function setAuthorizer(?callable $callback): bool {}
}

class SQLite3Stmt
{
    private function __construct(SQLite3 $sqlite3, string $query) {}

    #[TentativeType]
    public function bindParam(string|int $param, mixed &$var, int $type = SQLITE3_TEXT): bool {}

    #[TentativeType]
    public function bindValue(string|int $param, mixed $value, int $type = SQLITE3_TEXT): bool {}

    #[TentativeType]
    public function clear(): bool {}

    #[TentativeType]
    public function close(): true {}

    #[TentativeType]
    public function execute(): SQLite3Result|false {}

    #[TentativeType]
    public function getSQL(bool $expand = false): string|false {}

    #[TentativeType]
    public function paramCount(): int {}

    #[TentativeType]
    public function readOnly(): bool {}

    #[TentativeType]
    public function reset(): bool {}

    public function busy(): bool {}

    public const int EXPLAIN_MODE_PREPARED = 0;
    public const int EXPLAIN_MODE_EXPLAIN = 1;
    public const int EXPLAIN_MODE_EXPLAIN_QUERY_PLAN = 2;

    public function explain(): int {}

    public function setExplain(int $mode): bool {}
}

class SQLite3Result
{
    private function __construct() {}

    #[TentativeType]
    public function numColumns(): int {}

    #[TentativeType]
    public function columnName(int $column): string|false {}

    #[TentativeType]
    public function columnType(int $column): int|false {}

    /**
     * @return ($mode is 2 ? non-empty-list<mixed>|false : ($mode is 1 ? non-empty-array<string, mixed>|false : non-empty-array<int|string, mixed>|false))
     */
    #[TentativeType]
    public function fetchArray(int $mode = SQLITE3_BOTH): array|false {}

    /**
     * @return ($mode is 2 ? list<non-empty-list<mixed>>|false : ($mode is 1 ? list<non-empty-array<string, mixed>>|false : list<non-empty-array<int|string, mixed>>|false))
     */
    public function fetchAll(int $mode = SQLITE3_BOTH): array|false {}

    #[TentativeType]
    public function reset(): bool {}

    #[TentativeType]
    public function finalize(): true {}
}
