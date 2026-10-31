<?php

declare(strict_types=1);

namespace BoolVariableFromACallKeepsItsNarrowing;

final class Offer
{
    private string $type = 'room';

    public function isRoom(): bool
    {
        return $this->type === 'room';
    }

    public function becomeFlat(): void
    {
        $this->type = 'flat';
    }
}

/**
 * @mago-expect analysis:redundant-isset-check
 */
function in_array_flag(string $key): void
{
    $keys = ['a' => 1, 'b' => 2];
    $known = in_array($key, ['a', 'b'], true);
    if ($known && isset($keys[$key])) {
        echo $keys[$key];
    }
}

function method_flag(Offer $offer): void
{
    $wasRoom = $offer->isRoom();
    $offer->becomeFlat();
    if ($wasRoom && !$offer->isRoom()) {
        echo 1;
    }
}
