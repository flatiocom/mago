<?php

declare(strict_types=1);

final class Exporters
{
    /** @var array<class-string, string> */
    public static array $map = [];

    /** @var list<int> */
    public static array $ids = [];
}

function write_static_offsets(): void
{
    Exporters::$map[stdClass::class] = 'x';
    Exporters::$map[''] = 'x'; // @mago-expect analysis:invalid-property-assignment-value
    Exporters::$ids[] = 1;
    Exporters::$ids[] = 'two'; // @mago-expect analysis:invalid-property-assignment-value
}
