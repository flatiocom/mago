<?php

declare(strict_types=1);

function attempt(int $i): string
{
    return (string) $i;
}

/** @throws LogicException */
function retried(int $attempts): string
{
    for ($attempt = 1; ; $attempt++) {
        try {
            return attempt($attempt);
        } catch (RuntimeException $e) {
            if ($attempt < $attempts) {
                continue;
            }

            throw new LogicException('failed', 0, $e);
        }
    }
}

/** @return list<int> */
function paged(int $size): array
{
    $items = [];

    for ($offset = 0; ; $offset += $size) {
        if ($offset > 100) {
            return $items;
        }

        $items[] = $offset;
    }
}

/** @mago-expect analysis:missing-return-statement */
function left_by_break(int $limit): string
{
    for ($i = 1; ; $i++) {
        if ($i > $limit) {
            break;
        }
    }
}
