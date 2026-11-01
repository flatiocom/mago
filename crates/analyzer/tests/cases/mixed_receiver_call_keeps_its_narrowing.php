<?php

declare(strict_types=1);

function narrowed_call_on_mixed(mixed $item): bool
{
    // @mago-expect analysis:mixed-assignment
    // @mago-expect analysis:mixed-method-access
    // @mago-expect analysis:mixed-method-access
    $data = $item->getData() !== [] ? $item->getData() : null;
    if ($data === [] || $data === null) { // @mago-expect analysis:redundant-comparison,redundant-type-comparison
        return false;
    }

    return true;
}

function call_with_arguments_is_not_kept(mixed $item): bool
{
    // @mago-expect analysis:mixed-assignment
    // @mago-expect analysis:mixed-method-access
    // @mago-expect analysis:mixed-method-access
    $data = $item->getData(1) !== [] ? $item->getData(1) : null;
    if ($data === [] || $data === null) {
        return false;
    }

    return true;
}

function reassigned_receiver_is_not_kept(mixed $item, mixed $other): bool
{
    if ($item->getData() === []) { // @mago-expect analysis:mixed-method-access
        return false;
    }

    $item = $other; // @mago-expect analysis:mixed-assignment
    // @mago-expect analysis:mixed-assignment
    // @mago-expect analysis:mixed-method-access
    $data = $item->getData();
    if ($data === [] || $data === null) {
        return false;
    }

    return true;
}
