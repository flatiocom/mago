<?php

declare(strict_types=1);

final class Ticket
{
    public int $id = 1;
    public bool $forceClosed = false;

    public function update(): void
    {
    }
}

final class Tickets
{
    public function find(int $id)
    {
        return $id > 0 ? new Ticket() : null;
    }

    public function regenerate(Ticket $ticket): void
    {
        echo $ticket->id;
    }
}

final class TicketController
{
    private null|Ticket $ticket = null;

    public function __construct(
        private Tickets $tickets,
    ) {}

    public function reloadAfterAThisCall(bool $force): void
    {
        if ($this->ticket === null) {
            return;
        }

        if (!$this->ticket->forceClosed) {
            $id = $this->ticket->id;
            $this->requirePairing($this->ticket);
            if ($force) {
                $this->ticket = $this->tickets->find($id); // @mago-expect analysis:mixed-property-type-coercion
            }
        } else {
            $this->ticket->update();
        }

        if ($this->ticket) {
            echo 'kept';
        } else {
            echo 'deleted';
        }
    }

    public function keepWithoutAThisCall(Ticket $other): void
    {
        if ($this->ticket === null) {
            return;
        }

        if (!$this->ticket->forceClosed) {
            $this->tickets->regenerate($other);
        } else {
            $this->ticket->update();
        }

        if ($this->ticket) { // @mago-expect analysis:redundant-condition
            echo 'kept';
        } else { // @mago-expect analysis:unreachable-else-clause
            echo 'deleted';
        }
    }

    private function requirePairing(Ticket $ticket): void
    {
        echo $ticket->id;
    }
}
