<?php

declare(strict_types=1);

namespace ByReferenceCaptureOfAssignedVariable;

function install(): void
{
    $previous = set_error_handler(
        static function (int $severity, string $message, string $file, int $line) use (&$previous): bool {
            // @mago-expect analysis:mixed-operand
            return $previous !== null && (bool) $previous($severity, $message, $file, $line);
        },
    );
}
