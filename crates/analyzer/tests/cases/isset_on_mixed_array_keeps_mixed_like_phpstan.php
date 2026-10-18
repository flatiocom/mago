<?php

declare(strict_types=1);

/** @param array<array-key, mixed> $modules */
function takes_modules(array $modules): void
{
    echo count($modules);
}

/** @param array<string, mixed> $settings */
function nested_isset_keeps_the_mixed_parent(array $settings): void
{
    if (isset($settings['modules']['hooks'])) { // @mago-expect analysis:mixed-array-access
        takes_modules($settings['modules']); // @mago-expect analysis:mixed-argument
    }
}

function nested_read_after_isset(mixed $response): mixed
{
    if (isset($response['content']['type'])) { // @mago-expect analysis:mixed-array-access,mixed-array-access
        return $response['content']['type'];
    }

    return null;
}

function isset_on_a_mixed_base_is_never_redundant(mixed $post): int
{
    if (!is_array($post) || !isset($post['id']) || !is_int($post['id'])) {
        return 0;
    }

    return $post['id'];
}
