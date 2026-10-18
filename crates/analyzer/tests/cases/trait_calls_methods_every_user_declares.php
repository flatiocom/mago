<?php

declare(strict_types=1);

abstract class Page
{
    /**
     * @param list<mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return null;
    }
}

trait Greets
{
    public function greet(): string
    {
        return $this->needGreet(1);
    }

    public function greetWithWrongArgument(): string
    {
        return $this->needGreet('x'); // @mago-expect analysis:invalid-argument
    }

    public function greetAsInt(): int
    {
        return $this->needGreet(2); // @mago-expect analysis:invalid-return-statement
    }

    public function partialCall(): int
    {
        /** @mago-expect analysis:non-documented-method,mixed-return-statement */
        return $this->onlyAbout();
    }
}

final class Home extends Page
{
    use Greets;

    private function needGreet(int $id): string
    {
        return (string) $id;
    }
}

final class About extends Page
{
    use Greets;

    public function needGreet(int $id): string
    {
        return 'a' . $id;
    }

    public function onlyAbout(): int
    {
        return 1;
    }
}
