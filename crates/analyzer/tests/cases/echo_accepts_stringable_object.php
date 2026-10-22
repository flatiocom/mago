<?php

declare(strict_types=1);

namespace EchoAcceptsStringableObject;

final class Label
{
    public function __toString(): string
    {
        return 'label';
    }
}

final class Plain
{
}

function show(Label $label, ?Label $maybe, Plain $plain): void
{
    echo $label;
    echo $maybe;
    print $label;
    // @mago-expect analysis:invalid-argument
    echo $plain;
}
