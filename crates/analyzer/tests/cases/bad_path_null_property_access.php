<?php

class MyClass
{
    public string $name = '';
}

/**
 * @mago-expect analysis:null-property-access
 */
function testNullPropertyAccess(): string
{
    $obj = null;
    return $obj->name;
}
