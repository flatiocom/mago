<?php

declare(strict_types=1);

namespace CallableRequiredParameterWhereAcceptingIsOptional;

final class Handlers
{
    /** @var callable(mixed...): mixed */
    public $any;

    /** @param callable(int...): void $_callback */
    public function takeInts(callable $_callback): void
    {
    }

    /** @param callable(int=): void $_callback */
    public function takeOptionalInt(callable $_callback): void
    {
    }

    /** @param callable(int, int...): void $_callback */
    public function takeIntAndInts(callable $_callback): void
    {
    }

    /** @param \Closure(int=): void $_callback */
    public function takeClosureWithOptionalInt(\Closure $_callback): void
    {
    }

    /** @param callable(int): void $_callback */
    public function takeInt(callable $_callback): void
    {
    }

    public function sum(int $first, int $second): void
    {
        echo $first + $second;
    }

    public function withDefault(int $_value = 0): void
    {
    }

    /** @return callable(int=): void */
    public function giveOptionalInt(): callable
    {
        // @mago-expect analysis:invalid-return-statement
        return static function (int $_value): void {};
    }
}

function test_required_where_variadic(Handlers $handlers): void
{
    // @mago-expect analysis:invalid-property-assignment-value
    $handlers->any = static fn(mixed $value): mixed => $value;
    // @mago-expect analysis:invalid-argument
    $handlers->takeInts(static function (int $_first, int $_second): void {});
    // @mago-expect analysis:invalid-argument
    $handlers->takeIntAndInts(static function (int $_first, int $_second): void {});
    // @mago-expect analysis:possibly-invalid-argument
    $handlers->takeInts([$handlers, 'sum']);
}

function test_required_where_optional(Handlers $handlers): void
{
    // @mago-expect analysis:invalid-argument
    $handlers->takeOptionalInt(static function (int $_value): void {});
    // @mago-expect analysis:possibly-invalid-argument
    $handlers->takeClosureWithOptionalInt(static function (int $_value): void {});
}

function test_optional_or_variadic_where_optional(Handlers $handlers): void
{
    $handlers->any = static fn(mixed ...$values): int => count($values);
    $handlers->any = static fn(): int => 1;
    $handlers->takeInts(static function (int ...$_values): void {});
    $handlers->takeInts(static function (): void {});
    $handlers->takeInts([$handlers, 'withDefault']);
    $handlers->takeOptionalInt(static function (int $_value = 0): void {});
    $handlers->takeInt(static function (int $_value, int $_other = 0): void {});
}

function test_optional_past_the_accepting_parameters_is_not_compared(Handlers $handlers): void
{
    $handlers->takeInts(static function (int $_value = 0, string $_suffix = ''): void {});
    $handlers->takeInt(static function (int $_value, string ...$_suffixes): void {});
}
