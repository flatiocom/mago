<?php

declare(strict_types=1);

function narrowed_mixed_receiver_take(mixed $value): void
{
}

function narrowed_mixed_receiver_not_null(mixed $row): void
{
    if ($row !== null) {
        narrowed_mixed_receiver_take($row->name); // @mago-expect analysis:mixed-property-access
        narrowed_mixed_receiver_take($row->name); // @mago-expect analysis:mixed-property-access
    }
}

function narrowed_mixed_receiver_truthy(mixed $row): void
{
    if ($row) {
        narrowed_mixed_receiver_take($row->name); // @mago-expect analysis:mixed-property-access
        narrowed_mixed_receiver_take($row->name); // @mago-expect analysis:mixed-property-access
    }
}

function narrowed_mixed_receiver_isset_chain(mixed $row): void
{
    if (isset($row->order)) { // @mago-expect analysis:mixed-property-access
        narrowed_mixed_receiver_take($row->order);
        narrowed_mixed_receiver_take($row->order->customer); // @mago-expect analysis:mixed-property-access
        narrowed_mixed_receiver_take($row->order->customer); // @mago-expect analysis:mixed-property-access
    }
}
