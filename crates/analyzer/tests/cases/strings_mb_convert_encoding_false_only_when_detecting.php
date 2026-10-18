<?php

declare(strict_types=1);

function undo_double_encoding(string $value): string
{
    foreach (['ISO-8859-1', 'Windows-1252'] as $encoding) {
        $bytes = mb_convert_encoding($value, $encoding, 'UTF-8');

        if (mb_convert_encoding($bytes, 'UTF-8', $encoding) === $value && mb_check_encoding($bytes, 'UTF-8')) {
            return $bytes;
        }
    }

    return $value;
}

function to_utf8(string $value): string
{
    return mb_convert_encoding($value, 'UTF-8');
}

/**
 * @param list<string> $values
 *
 * @return list<string>
 */
function list_to_utf8(array $values): array
{
    return mb_convert_encoding($values, 'UTF-8', 'ISO-8859-1');
}

function detected_to_utf8(string $value): string
{
    /** @mago-expect analysis:falsable-return-statement,invalid-return-statement */
    return mb_convert_encoding($value, 'UTF-8', 'auto');
}

function candidates_to_utf8(string $value): string
{
    /** @mago-expect analysis:falsable-return-statement,invalid-return-statement */
    return mb_convert_encoding($value, 'UTF-8', 'ISO-8859-2, UTF-8');
}

/** @param list<string> $candidates */
function listed_candidates_to_utf8(string $value, array $candidates): string
{
    /** @mago-expect analysis:falsable-return-statement,invalid-return-statement */
    return mb_convert_encoding($value, 'UTF-8', $candidates);
}

function unknown_source_to_utf8(string $value, string $from): string
{
    /** @mago-expect analysis:falsable-return-statement,invalid-return-statement */
    return mb_convert_encoding($value, 'UTF-8', $from);
}

function wrong_return(string $value): int
{
    /** @mago-expect analysis:invalid-return-statement */
    return mb_convert_encoding($value, 'UTF-8');
}
