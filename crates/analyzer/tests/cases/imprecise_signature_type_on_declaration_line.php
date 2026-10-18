<?php

declare(strict_types=1);

final class Signatures
{
    #[\Deprecated] // @mago-expect analysis:imprecise-type
    public function attributeLine(
        string $name,
        array $values,
    ): void {
    }

    public function firstLine( // @mago-expect analysis:imprecise-type
        array $values,
    ): void {
    }

    public function returnOnLaterLine( // @mago-expect analysis:imprecise-type
        int $count,
    ): array {
        return [$count];
    }
}

function standalone( // @mago-expect analysis:imprecise-type
    array $values,
): void {
}
