<?php

declare(strict_types=1);

class Creator
{
    /** @var array<string, bool|int|string> */
    protected array $data = [];
}

final class PromotedCreator extends Creator
{
    /** @param array{type: string, discount: float} $data */
    public function __construct(
        protected array $data, // @mago-expect analysis:incompatible-property-type
    ) {}
}

final class DeclaredCreator extends Creator
{
    /** @var array<string, float> */
    protected array $data = []; // @mago-expect analysis:incompatible-property-type
}
