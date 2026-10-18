<?php

declare(strict_types=1);

/**
 * @param array<string, array{name: string, countries: array<string, mixed>}> $continents
 *
 * @return array<array-key, mixed>
 */
function offset_write_into_flipped_merge(array $continents, string $code, string $country, int $id): array
{
    $structure = array_merge(array_flip(['eu', 'na']), $continents);
    $structure[$code]['countries'] = ['cz' => ['cities' => []]]; // @mago-expect analysis:possibly-invalid-array-access,possibly-invalid-array-access
    $structure[$code]['countries'][$country]['cities'][$id] = 'x';
    $structure[$code]['countries'][$country]['regions'] = [];

    return $structure;
}

/**
 * @param int|array<string, int> $value
 *
 * @return array<string, int>
 */
function offset_write_into_int_or_array(int|array $value): array
{
    $value['a'] = 1; // @mago-expect analysis:possibly-invalid-array-access

    return $value;
}

/**
 * @param float|list<int> $value
 *
 * @return list<int>
 */
function offset_append_into_float_or_list(float|array $value): array
{
    $value[] = 1; // @mago-expect analysis:possibly-invalid-array-access

    return $value;
}
