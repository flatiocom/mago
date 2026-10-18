<?php

declare(strict_types=1);

function bool_only_false(int $i): bool // @mago-expect analysis:overly-wide-return-type
{
    if ($i > 0) {
        return false;
    }

    return false;
}

final class BoolHolder
{
    public function onlyTrue(int $i): bool // @mago-expect analysis:overly-wide-return-type
    {
        if ($i > 0) {
            return true;
        }

        return true;
    }

    public function single(): bool
    {
        return false;
    }

    public function both(int $i): bool
    {
        if ($i > 0) {
            return true;
        }

        return false;
    }

    public function computed(int $i): bool
    {
        return $i > 0;
    }
}

$bool_closure = static function (int $i): bool {
    if ($i > 0) {
        return false;
    }

    return false;
};
