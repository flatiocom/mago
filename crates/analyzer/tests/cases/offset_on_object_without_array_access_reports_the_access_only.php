<?php

declare(strict_types=1);

namespace OffsetOnObjectWithoutArrayAccessReportsTheAccessOnly;

final class Plain
{
    public int $count = 0;

    public function name(): string
    {
        return 'plain';
    }
}

function literal_offset(Plain $plain): mixed
{
    // @mago-expect analysis:invalid-array-access
    return $plain['x'];
}

function repeated_offset(Plain $plain, string $key): mixed
{
    // @mago-expect analysis:invalid-array-access
    if ($plain[$key]) {
        return $plain[$key];
    }

    return null;
}

/**
 * @param array<int, string>|Plain $list
 */
function list_or_plain(array|Plain $list): mixed
{
    // @mago-expect analysis:invalid-array-access,possibly-undefined-string-array-index
    return $list['x'];
}
