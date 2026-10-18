<?php

declare(strict_types=1);

interface Renderable
{
    public function redrawControl(): void;
}

class BaseUser
{
}

final class AdminUser extends BaseUser
{
    public function isAdmin(): bool
    {
        return true;
    }
}

/**
 * @property-read BaseUser $user
 */
abstract class Control implements Renderable
{
    public function redrawControl(null|string $snippet = null, bool $redraw = true): void
    {
    }

    public const USER_CLASS = BaseUser::class;

    public function getUser(): BaseUser
    {
        return new BaseUser();
    }

    public static function current(): BaseUser
    {
        return new BaseUser();
    }

    public function __get(string $name): mixed
    {
        return null;
    }
}

/**
 * @property-read AdminUser $user
 */
abstract class BaseController extends Control
{
    public const USER_CLASS = AdminUser::class;

    public function getUser(): AdminUser
    {
        return new AdminUser();
    }

    public static function current(): AdminUser
    {
        return new AdminUser();
    }
}

/**
 * @phpstan-require-extends BaseController
 */
trait BaseControllerTrait
{
    public function check(): bool
    {
        $this->redrawControl('snippet');

        return $this->user->isAdmin() && $this->getUser()->isAdmin() && $this->fluent()->getUser()->isAdmin();
    }

    public function checkStatic(): bool
    {
        $selfClass = self::USER_CLASS;
        $staticClass = static::USER_CLASS;

        return self::current()->isAdmin()
            && static::current()->isAdmin()
            && (new $selfClass())->isAdmin()
            && (new $staticClass())->isAdmin();
    }

    public function fluent(): static
    {
        return $this;
    }
}

abstract class DashboardController extends BaseController
{
    use BaseControllerTrait;
}
