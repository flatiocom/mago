<?php

declare(strict_types=1);

namespace TruthyMixedArrayIsNonEmpty;

/**
 * @mago-expect analysis:impossible-condition
 * @mago-expect analysis:mixed-assignment
 */
function loop_always_runs(mixed $recipients): void
{
    if (!$recipients) {
        return;
    }

    if (is_array($recipients)) {
        $none = true;
        foreach ($recipients as $recipient) {
            unset($recipient);
            $none = false;
        }

        if ($none) {
            echo 'none';
        }
    }
}

/**
 * @mago-expect analysis:mixed-assignment
 */
function possibly_empty(mixed $recipients): void
{
    if (is_array($recipients)) {
        $none = true;
        foreach ($recipients as $recipient) {
            unset($recipient);
            $none = false;
        }

        if ($none) {
            echo 'none';
        }
    }
}

function truthy_string_is_non_falsy(mixed $value): void
{
    if (!$value) {
        return;
    }

    if (is_string($value) && $value === '') { // @mago-expect analysis:impossible-condition,redundant-comparison,redundant-logical-operation
        echo 'empty';
    }
}

function truthy_int_is_non_zero(mixed $value): void
{
    if (!$value) {
        return;
    }

    if (is_int($value) && $value === 0) { // @mago-expect analysis:impossible-condition,redundant-comparison,redundant-logical-operation
        echo 'zero';
    }
}

function truthy_float_stays_float(mixed $value): void
{
    if (!$value) {
        return;
    }

    if (is_float($value) && $value === 0.0) {
        echo 'zero';
    }
}

function truthy_string_compared_with_a_non_falsy_value(mixed $value): void
{
    if (!$value) {
        return;
    }

    if (is_string($value) && $value === 'a') {
        echo 'a';
    }
}
