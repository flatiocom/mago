<?php

declare(strict_types=1);

function first_frame_file(): string
{
    foreach (debug_backtrace() as $frame) {
        if (isset($frame['file'])) {
            return $frame['file'];
        }
    }

    return '';
}
