<?php

declare(strict_types=1);

/** @deprecated */
interface DeprImplementedContract
{
}

/** @mago-expect analysis:deprecated-class */
final class DeprImplementedClass implements DeprImplementedContract
{
}

/** @mago-expect analysis:deprecated-class */
enum DeprImplementedEnum implements DeprImplementedContract
{
    case One;
}

/** @deprecated */
final class DeprImplementedLegacy implements DeprImplementedContract
{
}

function depr_implemented_anonymous(): object
{
    /** @mago-expect analysis:deprecated-class */
    return new class implements DeprImplementedContract {
    };
}
