<?php

declare(strict_types=1);

class ReturnBesideParameterParent
{
    /**
     * @param int $value
     *
     * @return int
     */
    public function get(mixed $value): mixed
    {
        return 1;
    }
}

class ReturnBesideParameterChild extends ReturnBesideParameterParent
{
    /**
     * @param string $value
     *
     * @return string
     *
     * @mago-expect analysis:incompatible-parameter-type
     * @mago-expect analysis:incompatible-return-type
     */
    public function get(mixed $value): mixed
    {
        return '';
    }
}
