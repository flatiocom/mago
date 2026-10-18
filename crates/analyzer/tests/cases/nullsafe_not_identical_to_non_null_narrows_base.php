<?php

declare(strict_types=1);

final class Seat
{
    public function __construct(
        public string $number,
    ) {}
}

final class Link
{
    public function __construct(
        public null|int $venueId,
        public null|Seat $seat,
    ) {}
}

function same_seat(null|string $number): bool
{
    return $number === '1';
}

function other_unit(int $venueId, null|Link $link): bool
{
    return $venueId !== $link?->venueId || same_seat($link->seat?->number);
}

function other_literal(null|Link $link): bool
{
    return $link?->venueId !== 5 || same_seat($link->seat?->number);
}

function same_unit_else(int $venueId, null|Link $link): bool
{
    if ($venueId !== $link?->venueId) {
        return false;
    }

    return same_seat($link->seat?->number);
}

function nullable_unit(null|int $venueId, null|Link $link): bool
{
    /** @mago-expect analysis:possibly-null-property-access */
    return $venueId !== $link?->venueId || same_seat($link->seat?->number);
}

function identical_unit(int $venueId, null|Link $link): bool
{
    /** @mago-expect analysis:possibly-null-property-access */
    return $venueId === $link?->venueId || same_seat($link->seat?->number);
}

function not_identical_branch(int $venueId, null|Link $link): bool
{
    if ($venueId !== $link?->venueId) {
        /** @mago-expect analysis:possibly-null-property-access */
        return same_seat($link->seat?->number);
    }

    return false;
}
