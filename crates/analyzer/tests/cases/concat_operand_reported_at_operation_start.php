<?php

declare(strict_types=1);

function mixed_operand_on_a_later_line(mixed $reason): string
{
    return '<p>Upload failed.</p>' // @mago-expect analysis:mixed-operand
        . '<p>Reason: '
        . $reason
        . '</p>';
}

function mixed_operand_on_the_first_line(mixed $reason): string
{
    return $reason . '</p>'; // @mago-expect analysis:mixed-operand
}

function parenthesized_chain_starts_its_own_operation(mixed $reason): string
{
    return '<p>'
        . ($reason // @mago-expect analysis:mixed-operand
            . '</p>');
}
