<?php

declare(strict_types=1);

final class Source
{
    public const string Web = 'web';
    public const Mobile = 'mobile';
    public const int Position = 3;
}

final class Steps
{
    private const string Own = 'own';

    private const array StepBySource = [Source::Web => 'step_web', Source::Mobile => 'step_mobile'];
    private const array OwnKeys = [self::Own => 1, 'literal' => 2];
    private const array PositionKeys = [Source::Position => 'third'];

    /** @return list<string> */
    public static function sources(): array
    {
        $sources = [];
        foreach (self::StepBySource as $source => $step) {
            $sources[] = $source;
        }

        return $sources;
    }

    /** @return list<'web'|'mobile'> */
    public static function exact_sources(): array
    {
        return array_keys(self::StepBySource);
    }

    /** @return list<string> */
    public static function own_keys(): array
    {
        return array_keys(self::OwnKeys);
    }

    public static function step(): string
    {
        return self::StepBySource['web'];
    }

    /** @return list<string> */
    public static function positions(): array
    {
        /** @mago-expect analysis:invalid-return-statement */
        return array_keys(self::PositionKeys);
    }
}
