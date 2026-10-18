<?php

class MyClass
{
    public string $name = '';
}

/**
 * @mago-expect analysis:possibly-null-property-access
 */
function testPossiblyNullPropertyAccess(?MyClass $obj): string
{
    return $obj->name;
}
