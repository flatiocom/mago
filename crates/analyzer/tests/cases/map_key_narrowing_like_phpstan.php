<?php

declare(strict_types=1);

final class LangLists
{
    /** @var array<string, array<string, int>> */
    private array $lists = [];

    /**
     * @return array<string, int>
     */
    public function isset_then_assign(string $lang): array
    {
        if (!isset($this->lists[$lang])) {
            $this->lists[$lang] = ['en' => 0, $lang => 1];
        }

        return $this->lists[$lang];
    }

    /**
     * @return array<string, int>
     */
    public function no_isset(string $lang): array
    {
        return $this->lists[$lang]; // @mago-expect analysis:possibly-undefined-string-array-index,nullable-return-statement,invalid-return-statement
    }
}

/**
 * @param array<string, array<string, float>> $links_of
 * @param array<int, list<string>> $references_by_batch
 * @return array<string, array<string, float>>
 */
function cast_key_isset(array $links_of, array $references_by_batch, string $id): array
{
    $matched = [];
    foreach ($references_by_batch as $batch => $references) {
        foreach ($references as $reference) {
            if (isset($links_of[(string) $batch][$id])) {
                $matched[(string) $batch][$reference] = $links_of[(string) $batch][$id];
            }
        }
    }

    return $matched;
}

/**
 * @param array<string, int> $map
 */
function cast_key_without_isset(array $map, int $key): int
{
    return $map[(string) $key]; // @mago-expect analysis:possibly-undefined-string-array-index,nullable-return-statement,invalid-return-statement
}

/**
 * @param array<string, int> $map
 */
function cast_key_reassigned(array $map, int $key): int
{
    if (isset($map[(string) $key])) {
        $key++;

        return $map[(string) $key]; // @mago-expect analysis:possibly-undefined-string-array-index,nullable-return-statement,invalid-return-statement
    }

    return 0;
}

/**
 * @param array<array-key, mixed> $link
 */
function concat_key_guarded(array $link): string
{
    $out = '';
    foreach (['a', 'b'] as $side) {
        $out .= is_string($link[$side . '@ref'] ?? null) ? $link[$side . '@ref'] : '';
    }

    return $out;
}

/**
 * @param array<string, string> $map
 */
function concat_key_unguarded(array $map, string $side): string
{
    return $map[$side . '@ref']; // @mago-expect analysis:possibly-undefined-string-array-index,nullable-return-statement,invalid-return-statement
}

/**
 * @param array<string, string> $labels
 * @return array<string, string>
 */
function concat_key_write_then_append(array $labels, int $id): array
{
    $labels['o' . $id] = 'order';
    $labels['o' . $id] .= ' (unpaid)';

    return $labels;
}

/**
 * @param array<string, array<string, int>> $lists
 * @return array<string, int>
 */
function local_isset_then_assign(array $lists, string $lang): array
{
    if (!isset($lists[$lang])) {
        $lists[$lang] = ['en' => 0];
    }

    return $lists[$lang];
}

/**
 * @param array<string, array{closed: list<string>, open: list<string>}|null> $skip
 * @return list<string>
 */
function nullable_array_closure_parameter_takes_shape(array $skip): array
{
    return array_merge(...array_map(
        static fn (array|null $documents): array => $documents === null ? [] : [...$documents['closed'], ...$documents['open']],
        array_values($skip),
    ));
}

/**
 * @param array<string, array<string, list<string>>|null> $skip
 */
function nullable_array_closure_parameter_takes_map(array $skip): int
{
    return count(array_map(
        static fn (array|null $documents): array => $documents === null ? [] : $documents['closed'], // @mago-expect analysis:possibly-undefined-string-array-index,nullable-return-statement,invalid-return-statement
        array_values($skip),
    ));
}

/**
 * @param list<mixed> $rows
 */
function nested_type_check_keeps_mixed_parent(array $rows): string
{
    $out = '';
    foreach ($rows as $row) { // @mago-expect analysis:mixed-assignment
        $symbol = is_scalar($row[8]) ? (string) $row[8] : ''; // @mago-expect analysis:mixed-array-access
        $amount = is_array($row) && isset($row[9]) && is_string($row[9]) ? $row[9] : '';
        if ($row[2] !== 'x') { // @mago-expect analysis:mixed-array-access
            $out .= $symbol . $amount;
        }
    }

    return $out;
}

function array_key_exists_makes_mixed_an_array(mixed $data): string
{
    if (!array_key_exists('status', $data)) { // @mago-expect analysis:mixed-argument
        return '';
    }

    return (string) $data['country']; // @mago-expect analysis:possibly-undefined-string-array-index,invalid-type-cast
}

/**
 * @return array{amount: int}
 */
function offset_write_creates_array(): array
{
    $values['amount'] = 5; // @mago-expect analysis:undefined-variable

    return $values;
}
