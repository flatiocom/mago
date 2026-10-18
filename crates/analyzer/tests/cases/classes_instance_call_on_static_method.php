<?php

declare(strict_types=1);

final class ClassesInstanceOnStatic
{
    public static function staticOnly(): int
    {
        return 1;
    }
}

// @mago-expect analysis:dynamic-static-method-call
echo (new ClassesInstanceOnStatic())->staticOnly();
