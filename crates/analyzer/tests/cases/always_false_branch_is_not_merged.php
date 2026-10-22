<?php

declare(strict_types=1);

final class AlwaysFalseBranchRow
{
    public int $id = 0;

    public function getId(): int
    {
        return $this->id;
    }
}

/**
 * @param list<AlwaysFalseBranchRow> $rows
 *
 * @return list<int>
 */
function previous_property_ids(array $rows): array
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
 * @param list<AlwaysFalseBranchRow> $rows
 *
 * @return list<int>
 */
function previous_method_ids(array $rows): array
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

function stored_condition_makes_branch_false(?int $value): int
{
    $wasNull = $value === null;
    $result = 1;
    // @mago-expect analysis:paradoxical-condition,impossible-condition,redundant-comparison,redundant-logical-operation
    if ($wasNull && $value !== null) {
        $result = 'changed';
    }

    return $result;
}
