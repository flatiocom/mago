<?php

declare(strict_types=1);

enum Setting: string
{
    case Services = 'services';

    /**
     * @param array<string, mixed> $settings
     */
    public static function dropService(array $settings, int $id): void
    {
        if (isset($settings[self::Services->value][$id])) { // @mago-expect analysis:mixed-array-access
            unset($settings[self::Services->value][$id]);
        }
    }
}

enum Slot: int
{
    case First = 0;
}

/**
 * @param array<int, mixed> $rows
 */
function enum_int_value_nested_unset(array $rows): void
{
    if (isset($rows[Slot::First->value]['entry'])) { // @mago-expect analysis:mixed-array-access
        unset($rows[Slot::First->value]['entry']);
    }
}

/**
 * @param array<string, mixed> $settings
 */
function enum_value_nested_unset(array $settings, int $id): void
{
    if (isset($settings[Setting::Services->value]['service'][$id])) { // @mago-expect analysis:mixed-array-access(2)
        unset($settings[Setting::Services->value]['service'][$id]);
    }
}

/**
 * @param array<string, mixed> $settings
 */
function enum_value_unset_without_isset(array $settings): void
{
    if ($settings[Setting::Services->value]) { // @mago-expect analysis:possibly-undefined-string-array-index
        unset($settings[Setting::Services->value]['entry']); // @mago-expect analysis:mixed-array-access
    }

    unset($settings[Setting::Services->value]['entry']); // @mago-expect analysis:mixed-array-access
}

/**
 * @param array<string, mixed> $settings
 */
function enum_name_is_not_the_value_key(array $settings): void
{
    if (isset($settings[Setting::Services->value]['entry'])) { // @mago-expect analysis:mixed-array-access
        unset($settings[Setting::Services->name]['entry']); // @mago-expect analysis:mixed-array-access
    }
}
