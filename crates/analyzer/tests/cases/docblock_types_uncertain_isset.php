<?php

declare(strict_types=1);

/**
 * @param array{type: string} $data
 */
function unproven_key_in_branch(array $data): string
{
    if (isset($data['item_id'])) { // @mago-expect analysis:impossible-nonnull-entry-check
        return (string) $data['item_id']; // @mago-expect analysis:invalid-type-cast
    }

    return '';
}

/**
 * @param array{type: string} $data
 */
function unproven_key_after_guard(array $data): string
{
    if (!isset($data['item_id'])) { // @mago-expect analysis:impossible-nonnull-entry-check
        return '';
    }

    return (string) $data['item_id']; // @mago-expect analysis:invalid-type-cast
}

/**
 * @param array{type: string} $data
 */
function key_read_outside_the_check(array $data): string
{
    if (isset($data['item_id'])) { // @mago-expect analysis:impossible-nonnull-entry-check
        echo 'set';
    }

    return $data['type'];
}
