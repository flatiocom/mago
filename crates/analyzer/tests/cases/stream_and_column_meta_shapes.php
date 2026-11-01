<?php

declare(strict_types=1);

namespace StreamAndColumnMetaShapes;

/**
 * @param resource $handle
 *
 * @mago-expect analysis:possibly-undefined-string-array-index
 */
function protocol($handle): string
{
    return stream_get_meta_data($handle)['crypto']['protocol'];
}

/**
 * @param resource $handle
 */
function checked_protocol($handle): string
{
    $meta = stream_get_meta_data($handle);

    return isset($meta['crypto']) ? $meta['crypto']['protocol'] : '';
}

/**
 * @param resource $handle
 *
 * @mago-expect analysis:possibly-undefined-string-array-index
 */
function media_type($handle): string
{
    return stream_get_meta_data($handle)['mediatype'];
}

/**
 * @return list<string>
 */
function flags(\PDOStatement $statement): array
{
    $meta = $statement->getColumnMeta(0);
    if ($meta === false) {
        return [];
    }

    return array_values($meta['flags']);
}
