<?php

declare(strict_types=1);

interface Node
{
}

final class Controller implements Node
{
    public function addMessage(string $message): void
    {
    }
}

final class Unrelated
{
}

/**
 * @template T of Node
 *
 * @param class-string<T> $type
 *
 * @return T
 *
 * @throws LogicException
 */
function lookup(string $type): Node
{
    throw new LogicException('stub');
}

/**
 * @param class-string<Controller> $type
 */
function controllerOnly(string $type): void
{
}

/**
 * @param class-string $type
 */
function anyClass(string $type): void
{
}

lookup('\Controller')->addMessage('saved');
lookup('Controller')->addMessage('saved');
controllerOnly('\Controller');
anyClass('\Controller');

// @mago-expect analysis:possibly-invalid-argument,unresolvable-template-type
lookup('\Unrelated');

/**
 * @param Controller::class $type
 */
function exactlyController(string $type): void
{
}

exactlyController('\Controller');

// @mago-expect analysis:invalid-argument
exactlyController('\Unrelated');
