<?php

declare(strict_types=1);

namespace Md5AndSha1ReturnTypes;

/**
 * @mago-expect analysis:redundant-comparison
 */
function md5_empty(string $s): bool
{
    return md5($s) === '';
}

/**
 * @mago-expect analysis:redundant-comparison
 */
function sha1_upper(string $s): bool
{
    return sha1($s) === 'ABC';
}

/**
 * @mago-expect analysis:redundant-comparison
 */
function raw_md5_falsy(string $s): bool
{
    return md5($s, true) === '0';
}

function raw_md5_upper(string $s): bool
{
    return md5($s, true) === "\xc4\xca\x42\x38\xa0\xb9\x23\x82\x0d\xcc\x50\x9a\x6f\x75\x84\x9b";
}

function hex_md5(string $s): bool
{
    return md5($s) === 'c4ca4238a0b923820dcc509a6f75849b';
}
