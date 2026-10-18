<?php

declare(strict_types=1);

function unused_function_parameter(int $unused): int
{
    return 1;
}

final class FormHandler
{
    // @mago-expect analysis:unused-parameter
    public function __construct(
        int $unused,
        private int $promoted,
        int $used,
    ) {
        echo $used;
    }

    public function onSuccess(object $form, array $values): void
    {
        echo count($values);
    }

    public function onError(object $form): void
    {
    }

    private function helper(int $unused): int
    {
        return 1;
    }

    public function run(): int
    {
        return $this->helper(1) + $this->promoted;
    }
}

interface ConstructedByContract
{
    public function __construct(int $value);
}

final class FulfilsConstructorContract implements ConstructedByContract
{
    public function __construct(int $value)
    {
    }
}

#[Attribute]
final class MarkerAttribute
{
    public function __construct(string $name)
    {
    }
}

abstract class BaseService
{
    public function __construct(protected int $id)
    {
    }
}

final class ChildService extends BaseService
{
    // @mago-expect analysis:unused-parameter
    public function __construct(int $id, string $label)
    {
        parent::__construct($id);
    }
}

/**
 * @param Closure(object, array<mixed>): void $handler
 */
function register_form_handler(Closure $handler): void
{
    $handler(new stdClass(), []);
}

register_form_handler(function (object $form, array $values): void {
    echo count($values);
});

// @mago-expect analysis:unused-parameter
register_form_handler(function (object $form, array $values): void {
    echo $form::class;
});

// @mago-expect analysis:unused-parameter
// @mago-expect analysis:unused-parameter
register_form_handler(function (object $form, array $values): void {
    echo 1;
});

/**
 * @param Closure(int, int): int $reducer
 */
function reduce_pair(Closure $reducer): int
{
    return $reducer(1, 2);
}

echo reduce_pair(fn(int $carry, int $item): int => $item);

// @mago-expect analysis:unused-parameter
echo reduce_pair(fn(int $carry, int $item): int => $carry);

echo unused_function_parameter(1);
echo (new FormHandler(1, 2, 3))->run();
echo (new ChildService(1, 'a'))::class;
echo (new FulfilsConstructorContract(1))::class;
echo (new MarkerAttribute('a'))::class;
