<?php

declare(strict_types=1);

namespace App {
    final class FooBar
    {
    }

    // @mago-expect analysis:incorrect-class-like-casing
    /**
     * @property-read fooBar $magic
     */
    class Holder
    {
        /** @mago-expect analysis:incorrect-class-like-casing */
        public null|fooBar $native = null;

        // @mago-expect analysis:incorrect-class-like-casing
        /** @var FOOBAR|null */
        public $untyped = null;

        /** @mago-expect analysis:incorrect-class-like-casing */
        public function param(foobar $p): void
        {
            echo $p::class;
        }

        /** @mago-expect analysis:incorrect-class-like-casing */
        public function nullableReturn(): ?FOOBAR
        {
            return null;
        }

        // @mago-expect analysis:incorrect-class-like-casing
        /**
         * @return Foobar
         */
        public function docblockReturn(): FooBar
        {
            return new FooBar();
        }

        /** @mago-expect analysis:incorrect-class-like-casing */
        public function internalUnion(null|\datetime $d): void
        {
            echo $d === null ? 1 : 2;
        }

        public function expressions(object $o): void
        {
            /** @mago-expect analysis:incorrect-class-like-casing */
            echo $o instanceof foobar ? 1 : 2;

            try {
                echo 1;
            /** @mago-expect analysis:incorrect-class-like-casing */
            } catch (\exception $e) {
                echo $e->getMessage();
            }
        }

        /** @return list<self> */
        public function correct(FooBar $p, \DateTime $d, object $o, self $s): array
        {
            echo $d->format('Y');
            echo $o instanceof FooBar ? 1 : 2;
            $f = static fn (self $x): self => $x;

            echo $p::class;

            /** @var self|null $same */
            $same = $o instanceof self ? $o : null;
            echo $same === null ? 1 : 2;

            /** @var static $late */
            $late = $s;
            echo $late::class;

            return [$f($s)];
        }
    }
}

namespace App\Usage {
    use App\FooBar;
    use App\FooBar as Renamed;
    use App as Aliased;
    use DateTimeImmutable;

    /** @mago-expect analysis:incorrect-class-like-casing */
    function imported(fooBar $p): void
    {
        echo $p::class;
    }

    /** @mago-expect analysis:incorrect-class-like-casing */
    function importedInternal(DateTimeimmutable $d): void
    {
        echo $d->format('Y');
    }

    function inlineVar(object $o): void
    {
        // @mago-expect analysis:incorrect-class-like-casing
        /** @var \App\FOOBAR $v */
        $v = $o;
        echo $v::class;

        // @mago-expect analysis:incorrect-class-like-casing
        /** @var list<\App\fooBar> $list */
        $list = [$o];
        echo count($list);

        /** @var FooBar $correct */
        $correct = $o;
        echo $correct::class;
    }

    function importedCorrect(FooBar $p, Renamed $r, renamed $q, Aliased\foobar $a): FooBar
    {
        echo $r::class . $q::class . $a::class;

        return new foobar();
    }
}
