<?php

declare(strict_types=1);

final class Repository
{
    public function find(): null|Row
    {
        return new Row();
    }
}

final class Row
{
}

function take_row(Row $_): void {}

abstract class Page
{
    public function __get(string $name): mixed
    {
        return null;
    }
}

trait Loads
{
    public function load(): void
    {
        $row = $this->repository->find();
        if ($row !== null) {
            take_row($row);
        }
    }

    public function language(): string
    {
        return $this->language; // @mago-expect analysis:invalid-return-statement
    }

    public function partial(): int
    {
        /** @mago-expect analysis:non-existent-property,mixed-return-statement */
        return $this->onlyHome;
    }
}

final class Home extends Page
{
    use Loads;

    public int $onlyHome = 1;

    public function __construct(
        private readonly Repository $repository,
        public string $language = '',
    ) {}
}

final class About extends Page
{
    use Loads;

    public Repository $repository;

    public int|string $language = '';

    public function __construct()
    {
        $this->repository = new Repository();
    }
}
