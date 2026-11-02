<?php

declare(strict_types=1);

final class Component
{
}

function method_on_an_offset_of_a_plain_object(Component $component, string $key): void
{
    // @mago-expect analysis:invalid-array-access
    // @mago-expect analysis:mixed-method-access
    $component[$key]->reset();
}

function narrowed_chain_rechecks_the_object_offset(Component $component, string $key): void
{
    // @mago-expect analysis:invalid-array-access
    // @mago-expect analysis:mixed-array-access
    if ($component[$key][$key]) {
        // @mago-expect analysis:invalid-array-access
        // @mago-expect analysis:mixed-method-access
        $component[$key][$key]->reset();
    }
}
