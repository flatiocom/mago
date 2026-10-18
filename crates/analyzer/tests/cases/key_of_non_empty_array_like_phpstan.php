<?php

declare(strict_types=1);

/**
 * @param array<string, list<int>> $groups
 */
function first_group_key(array $groups): string
{
    if ($groups === []) {
        return '';
    }

    return key($groups);
}

/**
 * @param array<string, list<int>> $groups
 */
function first_group_key_of_maybe_empty(array $groups): string
{
    /** @mago-expect analysis:nullable-return-statement,invalid-return-statement */
    return key($groups);
}
