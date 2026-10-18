<?php

declare(strict_types=1);

function coalesce_below_a_set_index(string $key): string
{
    $values = explode('|', $key);
    if (isset($values[3])) {
        /** @mago-expect analysis:redundant-null-coalesce */
        $below = $values[2] ?? 'x';

        return $below . $values[3] . ($values[4] ?? 'y');
    }

    return '';
}

function isset_below_a_set_index(string $key): bool
{
    $values = explode('|', $key);
    if (isset($values[3])) {
        /** @mago-expect analysis:redundant-isset-check */
        $below = isset($values[1]);
        echo isset($values[5]) ? 'y' : 'n';

        return $below;
    }

    return false;
}

/**
 * @param list<string> $line
 */
function below_a_truthy_index(array $line): string
{
    if (empty($line[7])) {
        return '';
    }

    /** @mago-expect analysis:redundant-null-coalesce */
    return $line[7] . ($line[6] ?? 'x');
}
