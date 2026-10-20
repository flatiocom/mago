<?php

declare(strict_types=1);

final class DeprClosureRegistry
{
    /** @deprecated */
    public function service(): int
    {
        return 1;
    }
}

final class DeprClosureUser
{
    /** @deprecated */
    public function legacy(DeprClosureRegistry $registry): int
    {
        $closure = static function () use ($registry): int {
            return $registry->service();
        };
        $arrow = static fn (): int => $registry->service();

        return $closure() + $arrow();
    }

    public function current(DeprClosureRegistry $registry): int
    {
        /** @mago-expect analysis:deprecated-method */
        $arrow = static fn (): int => $registry->service();

        return $arrow();
    }
}

/** @deprecated */
final class DeprClosureLegacyHost
{
    public function build(DeprClosureRegistry $registry): object
    {
        return new class ($registry) {
            public function __construct(
                private DeprClosureRegistry $registry,
            ) {
            }

            public function run(): int
            {
                /** @mago-expect analysis:deprecated-method */
                return $this->registry->service();
            }
        };
    }
}
