<?php

declare(strict_types=1);

final class Period
{
    public function __construct(
        private null|DateTime $from,
        private null|DateTime $to,
    ) {}

    public function getFrom(): null|DateTime
    {
        return $this->from;
    }

    public function getTo(): null|DateTime
    {
        return $this->to;
    }

    public function contains(DateTime $date): bool
    {
        if ($this->getFrom() === null || $this->getTo() === null) {
            return true;
        }

        return $this->getFrom() <= $date && $date <= $this->getTo();
    }

    public function endsAfter(DateTime $date): bool
    {
        if ($this->getFrom() === null || $date < new DateTime()) {
            return true;
        }

        return $date <= $this->getTo(); // @mago-expect analysis:possibly-null-operand
    }
}
