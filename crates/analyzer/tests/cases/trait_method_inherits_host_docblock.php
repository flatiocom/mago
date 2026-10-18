<?php

declare(strict_types=1);

abstract class Sender
{
    /**
     * @param-out string|null $message
     */
    public function send(string &$message): void
    {
        $message = null;
    }
}

trait ResendsWithoutTag
{
    public function send(string &$message): void
    {
        parent::send($message);
    }
}

final class Mailer extends Sender
{
    use ResendsWithoutTag;
}

trait ForwardsWithoutTag
{
    public function send(string &$message): void
    {
        parent::send($message);
    }
}

final class Forwarder extends Sender
{
    use ForwardsWithoutTag;
}

final class Notifier extends Sender
{
    use ForwardsWithoutTag;
}
