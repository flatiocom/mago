<?php

declare(strict_types=1);

final class Customer
{
    public string $name = '';

    public function touch(): void
    {
    }
}

final class Order
{
    public ?Customer $customer = null;

    public ?string $currency = null;

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function reset(): void
    {
    }

    /**
     * @phpstan-impure
     */
    public function impure(): int
    {
        return 1;
    }
}

final class Row
{
    public ?Order $order = null;

    public function save(): void
    {
    }

    public function count(): int
    {
        return 1;
    }
}

final class Other
{
    public function count(): int
    {
        return 1;
    }

    public function done(): void
    {
    }

    public function take(object $object): int
    {
        return $object instanceof Row ? 1 : 0;
    }

    public function takeVoid(object $object): void
    {
        echo $object instanceof Row ? 1 : 0;
    }
}

function needCustomer(Customer $customer): string
{
    return $customer->name;
}

function countRow(Row $row): int
{
    return $row->count();
}

function finishRow(Row $row): void
{
    $row->save();
}

function keeps_across_a_call_on_another_object(Row $row, Other $other): ?string
{
    if ($row->order === null) {
        return null;
    }

    $other->count();
    $other->done();

    return $row->order->currency;
}

function keeps_across_a_non_void_call_on_the_receiver(Row $row): ?string
{
    if ($row->order === null) {
        return null;
    }

    $row->count();

    return $row->order->currency;
}

function keeps_across_an_object_argument_of_a_non_void_call(Row $row, Row $second, Other $other): ?string
{
    if ($row->order === null) {
        return null;
    }

    $other->take($row);
    countRow($row);
    $other->takeVoid($second);

    return $row->order->currency;
}

function forgets_after_a_void_call_on_the_receiver(Row $row): ?string
{
    if ($row->order === null) {
        return null;
    }

    $row->save();

    return $row->order->currency; // @mago-expect analysis:possibly-null-property-access
}

function forgets_after_a_void_call_on_a_nested_receiver(Row $row): string
{
    if ($row->order === null || $row->order->customer === null) {
        return '';
    }

    $row->order->reset();
    echo $row->order->currency;

    return needCustomer($row->order->customer); // @mago-expect analysis:possibly-null-argument
}

function forgets_after_an_impure_call_on_the_receiver(Row $row): string
{
    if ($row->order === null || $row->order->customer === null) {
        return '';
    }

    $row->order->impure();

    return needCustomer($row->order->customer); // @mago-expect analysis:possibly-null-argument
}

function forgets_after_an_object_argument_of_a_void_call(Row $row, Other $other): ?string
{
    if ($row->order === null) {
        return null;
    }

    $other->takeVoid($row);

    return $row->order->currency; // @mago-expect analysis:possibly-null-property-access
}

function forgets_after_an_object_argument_of_a_void_function(Row $row): ?string
{
    if ($row->order === null) {
        return null;
    }

    finishRow($row);

    return $row->order->currency; // @mago-expect analysis:possibly-null-property-access
}

function keeps_a_method_result_across_calls(Order $order, Other $other): string
{
    if ($order->getCustomer() === null) {
        return '';
    }

    $other->count();
    $other->done();

    return needCustomer($order->getCustomer());
}

function forgets_a_method_result_after_a_void_call_on_its_receiver(Order $order): string
{
    if ($order->getCustomer() === null) {
        return '';
    }

    $order->reset();

    return needCustomer($order->getCustomer()); // @mago-expect analysis:possibly-null-argument
}

function keeps_a_property_of_a_method_result(Order $order, Other $other): string
{
    if ($order->getCustomer()?->name === null) {
        return '';
    }

    $other->count();

    return $order->getCustomer()->name;
}

final class Holder
{
    private ?Row $row = null;

    /**
     * @var (Closure(): int)|null
     */
    private ?Closure $provider = null;

    public function __construct(
        private readonly Other $other,
    ) {}

    /**
     * @param Closure(): int $provider
     */
    public function attach(Row $row, Closure $provider): void
    {
        $this->row = $row;
        $this->provider = $provider;
    }

    public function keeps_after_a_non_void_call_on_this(): ?Order
    {
        if ($this->row === null) {
            return null;
        }

        $this->count();

        return $this->row->order;
    }

    public function keeps_after_a_void_call_on_another_property(): ?Order
    {
        if ($this->row === null) {
            return null;
        }

        $this->other->done();

        return $this->row->order;
    }

    public function keeps_after_passing_this_to_a_non_void_call(): ?Order
    {
        if ($this->row === null) {
            return null;
        }

        $this->other->take($this);

        return $this->row->order;
    }

    public function forgets_after_a_void_call_on_this(): ?Order
    {
        if ($this->row === null) {
            return null;
        }

        $this->done();

        return $this->row->order; // @mago-expect analysis:possibly-null-property-access
    }

    public function forgets_after_a_void_static_call_on_self(): ?Order
    {
        if ($this->row === null) {
            return null;
        }

        self::finish();

        return $this->row->order; // @mago-expect analysis:possibly-null-property-access
    }

    public function forgets_after_passing_this_to_a_void_call(): ?Order
    {
        if ($this->row === null) {
            return null;
        }

        $this->other->takeVoid($this);

        return $this->row->order; // @mago-expect analysis:possibly-null-property-access
    }

    public function forgets_after_calling_a_typed_closure(): ?Order
    {
        if ($this->row === null || $this->provider === null) {
            return null;
        }

        ($this->provider)();

        return $this->row->order; // @mago-expect analysis:possibly-null-property-access
    }

    public function keeps_after_calling_a_bare_closure(Closure $closure): ?Order
    {
        if ($this->row === null) {
            return null;
        }

        $closure();

        return $this->row->order;
    }

    private function count(): int
    {
        return 1;
    }

    private function done(): void
    {
    }

    private static function finish(): void
    {
    }
}
