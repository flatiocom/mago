<?php

declare(strict_types=1);

function channel(string $channel): void
{
    echo $channel;
}

/**
 * @param list<int> $numbers
 */
function numbers(array $numbers): void
{
    echo count($numbers);
}

final class Channels
{
    public const string Email = 'email';

    /** @var list<string> */
    public const array Base = [self::Email, 'sms'];

    public const array All = [...self::Base, 'push'];
    public const array Named = ['first' => 1];
    public const array Merged = [...self::Named, 2];
    public const array Inline = [...[1, 2], 3];
}

function send_all(): void
{
    foreach (Channels::All as $channel) {
        channel($channel);
    }

    foreach (Channels::Merged as $number) {
        echo $number + 1;
    }

    numbers(Channels::Inline);
}
