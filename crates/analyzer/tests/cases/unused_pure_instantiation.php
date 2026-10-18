<?php

declare(strict_types=1);

final class UnusedInstantiationWithoutConstructor
{
}

final class UnusedInstantiationPureConstructor
{
    /**
     * @pure
     */
    public function __construct(
        public mixed $value = null,
    ) {}
}

final class UnusedInstantiationImpureConstructor
{
    public function __construct()
    {
        echo 'registered';
    }
}

final class UnusedInstantiationDestructorWithoutConstructor
{
    public function __destruct()
    {
        echo 'released';
    }
}

final class UnusedInstantiationDestructorWithConstructor
{
    public function __construct(
        public int $value,
    ) {}

    public function __destruct()
    {
        echo 'released';
    }
}

class UnusedInstantiationInferredParent
{
    protected string $label;

    public function __construct(string $label)
    {
        $this->label = strtolower($label);
    }
}

final class UnusedInstantiationInferredChild extends UnusedInstantiationInferredParent
{
    /** @var list<string> */
    private array $tags = [];

    public function __construct(string $label)
    {
        parent::__construct($label);
        $this->tags[] = $label;
        [$first] = [$label];
        $this->label .= $first;
    }

    public static function make(): void
    {
        new self('x');
    }
}

final class UnusedInstantiationSelfWithoutConstructor
{
    public static function make(): void
    {
        /** @mago-expect analysis:unused-statement */
        new self();

        /** @mago-expect analysis:unused-statement */
        new static();
    }
}

final class UnusedInstantiationCallsImpureMethod
{
    public function __construct()
    {
        $this->register();
    }

    private function register(): void
    {
        echo 'registered';
    }
}

final class UnusedInstantiationWritesForeignProperty
{
    public function __construct(UnusedInstantiationPureConstructor $target)
    {
        $target->value = 1;
    }
}

final class UnusedInstantiationCycleA
{
    public function __construct()
    {
        new UnusedInstantiationCycleB();
    }
}

final class UnusedInstantiationCycleB
{
    public function __construct()
    {
        new UnusedInstantiationCycleA();
    }
}

function unused_instantiation_side_effect(): string
{
    echo 'called';

    return 'x';
}

/**
 * @param class-string<UnusedInstantiationPureConstructor> $class
 */
function unused_instantiation_cases(string $class, int $counter): void
{
    /** @mago-expect analysis:unused-statement */
    new Exception('x');

    /** @mago-expect analysis:unused-statement */
    new UnusedInstantiationWithoutConstructor();

    /** @mago-expect analysis:unused-statement */
    new UnusedInstantiationPureConstructor(1);

    /** @mago-expect analysis:unused-statement */
    new UnusedInstantiationDestructorWithoutConstructor();

    /** @mago-expect analysis:unused-statement */
    new UnusedInstantiationDestructorWithConstructor(1);

    /** @mago-expect analysis:unused-statement */
    new UnusedInstantiationInferredParent('x');

    /** @mago-expect analysis:unused-statement */
    new UnusedInstantiationInferredChild(unused_instantiation_side_effect());

    /** @mago-expect analysis:unused-statement */
    new Exception('x', 0, new Exception('y'));

    /** @mago-expect analysis:unused-statement */
    new UnusedInstantiationPureConstructor($counter++);

    /** @mago-expect analysis:unused-statement */
    new UnusedInstantiationPureConstructor(strtoupper('x'));

    new UnusedInstantiationImpureConstructor();

    new UnusedInstantiationCallsImpureMethod();

    new UnusedInstantiationWritesForeignProperty(new UnusedInstantiationPureConstructor());

    new UnusedInstantiationCycleA();

    new Exception(unused_instantiation_side_effect());

    new UnusedInstantiationPureConstructor(new UnusedInstantiationImpureConstructor());

    new $class();
}

$kept = new Exception('x');

throw $kept;

class UnusedInstantiationQuietParent
{
}

final class UnusedInstantiationQuietParentChild extends UnusedInstantiationQuietParent
{
    public static function make(): void
    {
        /** @mago-expect analysis:unused-statement */
        new parent();
    }
}

class UnusedInstantiationNoisyParent
{
    public function __construct()
    {
        echo 'built';
    }
}

final class UnusedInstantiationNoisyParentChild extends UnusedInstantiationNoisyParent
{
    public static function make(): void
    {
        new parent();
    }
}
