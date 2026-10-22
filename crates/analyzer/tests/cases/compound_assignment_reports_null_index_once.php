<?php

declare(strict_types=1);

namespace CompoundAssignmentReportsNullIndexOnce;

/**
 * @param array<string, string> $groups
 *
 * @return array<string, string>
 */
function append(array $groups, ?string $key): array
{
    /** @mago-expect analysis:possibly-null-array-index,possibly-undefined-string-array-index */
    $groups[$key] .= 'x';

    return $groups;
}

/**
 * @param array<string, string> $groups
 *
 * @return array<string, string>
 */
function assign(array $groups, ?string $key): array
{
    /** @mago-expect analysis:possibly-null-array-index */
    $groups[$key] = 'x';

    return $groups;
}
