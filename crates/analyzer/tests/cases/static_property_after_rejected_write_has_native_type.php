<?php

declare(strict_types=1);

namespace StaticPropertyAfterRejectedWriteHasNativeType;

final class Settings
{
    /**
     * @var array{client: string}
     */
    private static array $params = ['client' => ''];

    public static function configure(mixed $value): void
    {
        // @mago-expect analysis:mixed-property-type-coercion
        self::$params = $value;
        // @mago-expect analysis:possibly-undefined-string-array-index
        self::use(self::$params['client']);
    }

    /**
     * @param array<string, string> $params
     */
    public static function replace(array $params): void
    {
        // @mago-expect analysis:invalid-property-assignment-value
        self::$params = $params;
        // @mago-expect analysis:possibly-undefined-string-array-index
        self::use(self::$params['client']);
    }

    public static function keep(): void
    {
        self::$params = ['client' => 'a'];
        self::use(self::$params['client']);
    }

    private static function use(mixed $value): void
    {
        echo serialize($value);
    }
}
