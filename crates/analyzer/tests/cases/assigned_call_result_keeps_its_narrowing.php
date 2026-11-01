<?php

declare(strict_types=1);

namespace AssignedCallResultKeepsItsNarrowing;

/**
 * @return 0|1|false
 *
 * @psalm-assert-if-true non-empty-string $value
 */
function check(mixed $value): int|false
{
    return is_string($value) && $value !== '' ? 1 : 0;
}

/**
 * @return non-empty-string|null
 */
function identical(mixed $value): ?string
{
    $result = check($value);
    if ($result !== 1) {
        return null;
    }

    return $value;
}

/**
 * @return non-empty-string|null
 */
function truthy(mixed $value): ?string
{
    $result = check($value);
    if (!$result) {
        return null;
    }

    return $value;
}

/**
 * @mago-expect analysis:mixed-return-statement
 * @mago-expect analysis:redundant-comparison
 */
function reassigned(mixed $value): ?string
{
    $result = check($value);
    $result = 1;
    if ($result !== 1) {
        return null;
    }

    return $value;
}
