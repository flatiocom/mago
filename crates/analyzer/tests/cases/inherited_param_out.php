<?php

declare(strict_types=1);

final class Row
{
}

abstract class Sender
{
    /**
     * @param-out Row|null $row
     */
    public function send(Row &$row): void
    {
        $row = null;
    }
}

final class OverrideWithoutTag extends Sender
{
    public function send(Row &$row): void
    {
        parent::send($row);
    }
}

final class OverrideWithOwnTag extends Sender
{
    /**
     * @param-out Row $row
     */
    public function send(Row &$row): void
    {
        /** @mago-expect analysis:reference-constraint-violation */
        parent::send($row);
    }
}

function caller_sees_inherited_out_type(OverrideWithoutTag $sender, Row $row): Row
{
    $sender->send($row);

    /** @mago-expect analysis:nullable-return-statement */
    /** @mago-expect analysis:invalid-return-statement */
    return $row;
}
