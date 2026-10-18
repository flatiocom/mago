<?php

declare(strict_types=1);

abstract class Page
{
    public string $title = '';

    public function render(): string
    {
        return $this->title;
    }

    /**
     * @param list<mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return null;
    }
}

final class Home extends Page
{
    use Titles;
    use PanelTools;

    public function homeOnly(): void
    {
    }
}

final class About extends Page
{
    use Titles;
    use PanelTools;
}

trait Titles
{
    public function upperTitle(): string
    {
        return strtoupper($this->render() . $this->title);
    }

    public function callsHomeOnly(): void
    {
        /** @mago-expect analysis:non-documented-method */
        $this->homeOnly();
    }
}

/**
 * @mixin Home
 */
trait PanelTools
{
    public function usesTheMixin(): void
    {
        $this->homeOnly();
    }
}

final class Counter
{
    use Counts;

    public int $count = 0;
}

trait Counts
{
    public function next(): int
    {
        return ++$this->count;
    }
}

abstract class Base
{
    public function wave(string $to): string
    {
        return $to;
    }
}

final class Host extends Base
{
    use WavesLikeTheParent;

    public function wave(string $to, string $from = ''): string
    {
        return $to . $from;
    }
}

trait WavesLikeTheParent
{
    public function waveFromHere(): string
    {
        /** @mago-expect analysis:too-many-arguments */
        return parent::wave('a', 'b');
    }
}
