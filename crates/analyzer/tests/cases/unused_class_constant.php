<?php

declare(strict_types=1);

namespace Fixture;

use Attribute;

#[Attribute]
final class Tagged
{
    public function __construct(
        public string $value,
    ) {}
}

trait HasLabel
{
    private const string Label = 'label';

    public function label(): string
    {
        return self::Label;
    }
}

enum Status: string
{
    private const string Prefix = 'status-';

    // @mago-expect analysis:unused-class-constant
    private const string Unused = 'unused';

    case Active = self::Prefix . 'active';
}

class Base
{
    public const string Overridden = 'base';

    // @mago-expect analysis:unused-class-constant
    private const string Shadowed = 'base';
}

class Constants extends Base
{
    // @mago-expect analysis:incompatible-constant-access
    private const string Overridden = 'child';

    // @mago-expect analysis:unused-class-constant
    private const string Shadowed = 'child';

    use HasLabel;

    // @mago-expect analysis:unused-class-constant
    private const string ErrorLog = 'error';

    private const int OnlyInUnusedMethod = 1;

    private const string _IntentionallyUnused = 'x';

    public const string Public = 'public';

    protected const string Protected = 'protected';

    private const string BySelf = 'self';

    private const string ByStatic = 'static';

    private const string ByName = 'name';

    private const string InConstant = 'constant';

    public const string Composed = self::InConstant . '!';

    private const string InPropertyDefault = 'property';

    private const string InParameterDefault = 'parameter';

    private const string InAttribute = 'attribute';

    private const string InClosure = 'closure';

    private const string ByInstance = 'instance';

    private string $property = self::InPropertyDefault;

    #[Tagged(self::InAttribute)]
    public function read(string $parameter = self::InParameterDefault): string
    {
        $closure = static fn(): string => self::InClosure;

        return self::BySelf . static::ByStatic . Constants::ByName . $this->property . $parameter . $closure() . $this::ByInstance;
    }

    // @mago-expect analysis:unused-method
    private function neverCalled(): int
    {
        return self::OnlyInUnusedMethod;
    }
}
