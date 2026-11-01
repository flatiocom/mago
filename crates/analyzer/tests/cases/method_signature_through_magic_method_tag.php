<?php

declare(strict_types=1);

class MagicTagGrandparent
{
    public function take(int $value): void
    {
    }

    public function keep(int $value): void
    {
    }
}

/**
 * @method void take(string $value)
 */
class MagicTagMiddle extends MagicTagGrandparent
{
}

final class MagicTagChild extends MagicTagMiddle
{
    /** @mago-expect analysis:incompatible-parameter-type */
    public function take(string $value): void
    {
    }

    public function keep(int $value): void
    {
    }
}
