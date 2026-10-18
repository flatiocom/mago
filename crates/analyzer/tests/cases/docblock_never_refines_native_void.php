<?php

declare(strict_types=1);

final class Responder
{
    /**
     * @return never
     *
     * @throws RuntimeException
     */
    public function abort(): void
    {
        throw new RuntimeException('abort');
    }
}

/**
 * @return never
 */
function stop(): void
{
    exit(1);
}

/**
 * @throws RuntimeException
 */
function after_method(Responder $responder, bool $flag): int
{
    if ($flag) {
        $value = 1;
    } else {
        $responder->abort();
    }

    return $value;
}

function after_function(bool $flag): int
{
    if ($flag) {
        $value = 1;
    } else {
        stop();
    }

    return $value;
}
