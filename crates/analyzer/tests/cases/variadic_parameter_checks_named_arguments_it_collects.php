<?php

declare(strict_types=1);

namespace VariadicParameterChecksNamedArgumentsItCollects;

function rest(int ...$numbers): int
{
    return count($numbers);
}

function first_and_rest(int $first, int ...$numbers): int
{
    return $first + count($numbers);
}

/**
 * @template T
 *
 * @param T ...$items
 *
 * @return list<T>
 */
function items(mixed ...$items): array
{
    return array_values($items);
}

function fill(mixed &...$references): int
{
    return count($references);
}

final class Numbers
{
    /** @var list<int> */
    public array $numbers;

    public function __construct(int ...$numbers)
    {
        $this->numbers = array_values($numbers);
    }

    public function count(int ...$numbers): int
    {
        return count($numbers);
    }
}

function wrong_types(Numbers $numbers): Numbers
{
    // @mago-expect analysis:invalid-argument
    echo rest(n: 'x');
    // @mago-expect analysis:invalid-argument
    echo first_and_rest(1, n: 'x');
    // @mago-expect analysis:invalid-argument
    echo rest(n: 1, m: 'y');
    // @mago-expect analysis:invalid-argument
    echo $numbers->count(n: 'x');
    // @mago-expect analysis:duplicate-named-argument
    echo rest(n: 1, n: 2);

    // @mago-expect analysis:invalid-argument
    return new Numbers(n: 'x');
}

function matching_types(): void
{
    echo rest(n: 1, m: 2);
    echo first_and_rest(first: 1, n: 2, m: 3);
    echo rest(1, n: 2);
}

function by_reference(): void
{
    // @mago-expect analysis:reference-to-undefined-variable
    echo fill(value: $value);
}

/**
 * @return list<int>
 */
function template_from_names(): array
{
    return items(a: 1, b: 2);
}
