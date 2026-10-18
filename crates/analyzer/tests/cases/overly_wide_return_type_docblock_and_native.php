<?php

declare(strict_types=1);

final class FileRow
{
}

/**
 * @return FileRow|array<FileRow>
 *
 * @mago-expect analysis:overly-wide-return-type
 */
function docblock_and_native_both_too_wide(): FileRow|array
{
    return [new FileRow()];
}

/**
 * @return ?FileRow
 *
 * @mago-expect analysis:overly-wide-return-type
 */
function docblock_and_native_both_nullable(): ?FileRow
{
    return new FileRow();
}

/**
 * @return list<FileRow>
 */
function docblock_narrows_native_and_fits(): FileRow|array
{
    return [new FileRow()];
}

/**
 * @return list<FileRow>
 */
function docblock_without_null_on_nullable_native_returning_null(bool $empty): ?array
{
    if ($empty) {
        return null;
    }

    return [new FileRow()];
}
