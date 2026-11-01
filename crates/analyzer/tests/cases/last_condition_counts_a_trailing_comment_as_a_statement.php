<?php

declare(strict_types=1);

namespace LastConditionCountsATrailingCommentAsAStatement;

/**
 * @throws \LogicException
 *
 * @mago-expect analysis:redundant-type-comparison(2)
 * @mago-expect analysis:unreachable-else-clause
 * @mago-expect analysis:unevaluated-code
 */
function comment_after_the_closing_throw(string $extension, int $kind): void
{
    if (!in_array($extension, ['pdf', 'png'], true)) {
        throw new \LogicException('extension');
    }

    if ($kind === 1) {
        echo 1;
    } elseif (in_array($extension, ['pdf', 'png'], true)) {
        echo 2;
    } else {
        throw new \LogicException('kind');
        // unreachable
    }

    if (in_array($extension, ['pdf', 'png'], true)) {
        return;
    }

    throw new \LogicException('kind'); // why
}

/**
 * @throws \LogicException
 *
 * @mago-expect analysis:unreachable-else-clause(2)
 */
function comment_before_the_closing_throw(string $extension, int $kind): void
{
    if (!in_array($extension, ['pdf', 'png'], true)) {
        throw new \LogicException('extension');
    }

    if ($kind === 1) {
        echo 1;
    } elseif (in_array($extension, ['pdf', 'png'], true)) {
        echo 2;
    } else {
        // cannot happen
        throw new \LogicException('kind');
    }

    if ($kind === 2) {
        echo 2;
    } elseif (in_array($extension, ['pdf', 'png'], true)) {
        echo 3;
    } else throw new \LogicException('kind'); // no block
}

/**
 * @throws \LogicException
 *
 * @mago-expect analysis:unevaluated-code
 */
function comment_at_the_end_of_a_case(string $extension, int $kind): void
{
    if (!in_array($extension, ['pdf', 'png'], true)) {
        throw new \LogicException('extension');
    }

    switch ($kind) {
        case 1:
            if (in_array($extension, ['pdf', 'png'], true)) {
                return;
            }
            throw new \LogicException('kind');
            // next case
        default:
            echo 1;
    }
}
