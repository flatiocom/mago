<?php

declare(strict_types=1);

final class ClassesWithDestructor
{
    public function __destruct()
    {
    }
}

/** @mago-expect analysis:unused-statement */
new ClassesWithDestructor();
