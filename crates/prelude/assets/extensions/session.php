<?php

interface SessionHandlerInterface
{
    #[TentativeType]
    public function close(): bool;

    #[TentativeType]
    public function destroy(string $id): bool;

    #[TentativeType]
    public function gc(int $max_lifetime): int|false;

    #[TentativeType]
    public function open(string $path, string $name): bool;

    #[TentativeType]
    public function read(string $id): string|false;

    #[TentativeType]
    public function write(string $id, string $data): bool;
}

interface SessionIdInterface
{
    #[TentativeType]
    public function create_sid(): string;
}

interface SessionUpdateTimestampHandlerInterface
{
    #[TentativeType]
    public function validateId(string $id): bool;

    #[TentativeType]
    public function updateTimestamp(string $id, string $data): bool;
}

class SessionHandler implements SessionHandlerInterface, SessionIdInterface
{
    #[TentativeType]
    public function close(): bool {}

    #[TentativeType]
    public function create_sid(): string {}

    #[TentativeType]
    public function destroy(string $id): bool {}

    #[TentativeType]
    public function gc(int $max_lifetime): int|false {}

    #[TentativeType]
    public function open(string $path, string $name): bool {}

    #[TentativeType]
    public function read(string $id): string|false {}

    #[TentativeType]
    public function write(string $id, string $data): bool {}
}

function session_name(?string $name = null): string|false {}

function session_module_name(?string $module = null): string|false {}

function session_save_path(?string $path = null): string|false {}

function session_id(?string $id = null): string|false {}

function session_regenerate_id(bool $delete_old_session = false): bool {}

function session_register_shutdown(): void {}

function session_decode(string $data): bool {}

function session_encode(): string|false {}

function session_start(array $options = []): bool {}

function session_create_id(string $prefix = ''): string|false {}

function session_gc(): int|false {}

function session_destroy(): bool {}

function session_unset(): bool {}

function session_set_save_handler(
    object|callable $open,
    callable|bool $close = UNKNOWN,
    callable $read = UNKNOWN,
    callable $write = UNKNOWN,
    callable $destroy = UNKNOWN,
    callable $gc = UNKNOWN,
    ?callable $create_sid = null,
    ?callable $validate_sid = null,
    ?callable $update_timestamp = null,
): bool {}

function session_cache_limiter(?string $value = null): string|false {}

function session_cache_expire(?int $value = null): int|false {}

function session_set_cookie_params(
    array|int $lifetime_or_options,
    ?string $path = null,
    ?string $domain = null,
    ?bool $secure = null,
    ?bool $httponly = null,
): bool {}

/**
 * @return array{
 *  lifetime: int,
 *  path: string,
 *  domain: string,
 *  secure: bool,
 *  httponly: bool,
 *  samesite: string
 * }
 */
function session_get_cookie_params(): array {}

function session_write_close(): bool {}

function session_commit(): bool {}

function session_status(): int {}

function session_abort(): bool {}

function session_reset(): bool {}
