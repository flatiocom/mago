<?php

declare(strict_types=1);

final class NativelyFalseBranchRow
{
    public int $id = 0;

    public function getId(): int
    {
        return $this->id;
    }
}

/**
 * @param list<NativelyFalseBranchRow> $rows
 *
 * @return list<int>
 */
function native_previous_property_ids(array $rows): array
{
    $ids = [];
    $previous = null;
    foreach ($rows as $row) {
        if ($previous !== null) {
            $ids[] = $previous->id;
        }

        $previous = $row;
    }

    return $ids;
}

/**
 * @param list<NativelyFalseBranchRow> $rows
 *
 * @return list<int>
 */
function native_previous_method_ids(array $rows): array
{
    $ids = [];
    $previous = null;
    foreach ($rows as $row) {
        if ($previous !== null) {
            $ids[] = $previous->getId();
        }

        $previous = $row;
    }

    return $ids;
}

function native_stored_condition_keeps_branch(?int $value): int
{
    $wasNull = $value === null;
    $result = 1;
    if ($wasNull && $value !== null) { // @mago-expect analysis:paradoxical-condition
        $result = 'changed';
    }

    return $result; // @mago-expect analysis:invalid-return-statement
}
