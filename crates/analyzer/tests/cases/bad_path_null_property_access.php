<?php

class MyClass
{
    public string $name = '';
}

/**
 * @mago-expect analysis:null-property-access,invalid-return-statement
 */
function testNullPropertyAccess(): string
{
    $obj = null;
    return $obj->name;
}
