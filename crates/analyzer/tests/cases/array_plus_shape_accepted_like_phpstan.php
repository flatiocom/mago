<?php

declare(strict_types=1);

/** @param array<string, mixed> $values */
function take_values(array $values): void
{
}

/** @param array<string, string> $values */
function take_strings(array $values): void
{
}

/** @param array{a: int} $values */
function take_sealed(array $values): void
{
}

/** @param array{a: int, ...<string, string>} $values */
function take_open_shape(array $values): void
{
}

/** @param iterable<string, mixed> $values */
function take_iterable(iterable $values): void
{
}

/** @param iterable<int, mixed> $values */
function take_int_iterable(iterable $values): void
{
}

/** @param iterable<string, string> $values */
function take_string_iterable(iterable $values): void
{
}

/** @param array<string, array<string, mixed>> $values */
function take_nested(array $values): void
{
}

final class Holder
{
    /** @var array<string, mixed> */
    public array $values = [];

    /** @var array<string, mixed> */
    public static array $shared = [];

    /** @var array<array-key, mixed> */
    private array $payload = [];

    /** @var array<string, mixed> */
    public array $hooked {
        get => ['a' => 1] + $this->payload;
    }

    /**
     * @param array<array-key, mixed> $payload
     * @return array<string, mixed>
     */
    public function shape_plus_array(array $payload): array
    {
        $this->payload = $payload;
        $this->values = ['a' => 1] + $payload;
        self::$shared = ['a' => 1] + $payload;

        return ['a' => 1] + $payload;
    }
}

/** @param array<array-key, mixed> $payload */
function shape_plus_array_accepted(array $payload, bool $flag): void
{
    take_values(['a' => 1] + $payload);
    take_values(['a' => 1] + $payload + $payload);

    $values = ['user_id' => 1] + $payload;
    if ($flag) {
        take_values($values);
    } else {
        take_values($values);
    }

    $values['b'] = 2;
    take_values($values);
    take_nested(['x' => ['a' => 1] + $payload]);
    take_iterable(['a' => 1] + $payload);
    take_int_iterable(['a' => 1] + $payload);
}

/**
 * @param array<array-key, mixed> $payload
 * @param array<int, mixed> $int_keyed
 */
function general_array_reported(array $payload, array $int_keyed, bool $flag): void
{
    take_values($payload + ['a' => 1]); // @mago-expect analysis:less-specific-argument
    take_values([] + $payload); // @mago-expect analysis:less-specific-argument
    take_strings(['a' => 1] + $payload); // @mago-expect analysis:possibly-invalid-argument
    take_sealed(['a' => 1] + $payload); // @mago-expect analysis:possibly-invalid-argument
    take_open_shape(['a' => 1] + $payload); // @mago-expect analysis:less-specific-argument
    take_iterable(['a' => 1] + $int_keyed); // @mago-expect analysis:possibly-invalid-argument
    take_iterable($payload + ['a' => 1]); // @mago-expect analysis:less-specific-argument
    take_string_iterable(['a' => 'x'] + $payload); // @mago-expect analysis:less-specific-nested-argument-type

    $copy = $payload;
    $copy['a'] = 1;
    take_values($copy); // @mago-expect analysis:less-specific-argument

    $either = $flag ? ['a' => 1] + $payload : ['b' => 1] + $payload;
    take_values($either); // @mago-expect analysis:less-specific-argument
}
