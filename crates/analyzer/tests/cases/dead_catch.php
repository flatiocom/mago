<?php

declare(strict_types=1);

class OrderException extends RuntimeException {}

final class PaymentException extends Exception {}

interface Retryable {}

final class Box
{
    public int $value = 0;
}

class OpenBox
{
    public int $value = 0;
}

final class Gateway
{
    /**
     * @throws PaymentException
     */
    public function charge(): void
    {
        throw new PaymentException('declined');
    }

    /**
     * @throws void
     */
    public function ping(): void {}

    public function log(): void {}
}

function echo_only(): void
{
    try {
        echo 1;
    } catch (RuntimeException $e) { // @mago-expect analysis:dead-catch
        echo $e->getMessage();
    }
}

function throwable_with_echo_only(): void
{
    try {
        echo 1;
    } catch (Throwable $e) { // @mago-expect analysis:dead-catch
        echo $e->getMessage();
    }
}

function call_without_throws_tag_can_throw_anything(Gateway $gateway): void
{
    try {
        $gateway->log();
    } catch (RuntimeException $e) {
        echo $e->getMessage();
    }
}

function throw_caught_by_parent(): void
{
    try {
        throw new OrderException('order');
    } catch (RuntimeException $e) {
        echo $e->getMessage();
    }
}

function throw_caught_by_child(Exception $exception): void
{
    try {
        throw $exception;
    } catch (OrderException $e) {
        echo $e->getMessage();
    }
}

function throw_of_unrelated_class(): void
{
    try {
        throw new OrderException('order');
    } catch (PaymentException $e) { // @mago-expect analysis:dead-catch
        echo $e->getMessage();
    }
}

function throws_tag_of_unrelated_class(Gateway $gateway): void
{
    try {
        $gateway->charge();
    } catch (OrderException $e) { // @mago-expect analysis:dead-catch
        echo $e->getMessage();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function throws_tag_against_interface(Gateway $gateway, OrderException $order): void
{
    try {
        $gateway->charge();
    } catch (Retryable $e) { // @mago-expect analysis:dead-catch
        echo $e::class;
    }

    try {
        throw $order;
    } catch (Retryable $e) {
        echo $e::class;
    }
}

function throws_void(Gateway $gateway): void
{
    try {
        $gateway->ping();
    } catch (Exception $e) { // @mago-expect analysis:dead-catch
        echo $e->getMessage();
    }
}

function builtin_function_without_arguments(string $value): void
{
    try {
        $now = time();
        echo $now;
    } catch (Exception $e) { // @mago-expect analysis:dead-catch
        echo $e->getMessage();
    }

    try {
        echo strlen($value);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function caught_by_earlier_clause(): void
{
    try {
        throw new OrderException('order');
    } catch (OrderException $e) {
        echo $e->getMessage();
    } catch (PaymentException $e) { // @mago-expect analysis:dead-catch
        echo $e->getMessage();
    }
}

function already_caught_above(Gateway $gateway): void
{
    try {
        $gateway->log();
    } catch (Exception $e) {
        echo $e->getMessage();
    } catch (RuntimeException $e) {
        echo $e->getMessage();
    }
}

function union_catch(): void
{
    try {
        throw new OrderException('order');
    } catch (OrderException|PaymentException $e) { // @mago-expect analysis:dead-catch
        echo $e->getMessage();
    }
}

function closure_body_does_not_run(): void
{
    try {
        $throw = static function (): never {
            throw new OrderException('order');
        };
    } catch (OrderException $e) { // @mago-expect analysis:dead-catch
        echo $e->getMessage();
    }
}

function property_reads(Box $box, OpenBox $openBox): void
{
    try {
        echo $box->value;
    } catch (Exception $e) { // @mago-expect analysis:dead-catch
        echo $e->getMessage();
    }

    try {
        echo $openBox->value;
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function string_conversion_of_object(Stringable $value): void
{
    try {
        echo "value: {$value}";
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function division(int $left, int $right): void
{
    try {
        echo $left % $right;
    } catch (DivisionByZeroError $e) { // @mago-expect analysis:avoid-catching-error
        echo $e->getMessage();
    }
}

function match_without_default(int $value): void
{
    try {
        echo match ($value) { // @mago-expect analysis:match-not-exhaustive
            1 => 'one',
            2 => 'two',
        };
    } catch (UnhandledMatchError $e) { // @mago-expect analysis:avoid-catching-error
        echo $e->getMessage();
    }
}

/**
 * @param list<int> $values
 */
function inside_loop(array $values): void
{
    foreach ($values as $value) {
        try {
            echo $value;
        } catch (RuntimeException $e) { // @mago-expect analysis:dead-catch
            echo $e->getMessage();
        }
    }
}
