<?php

namespace Mellivora\Http\Api\Contracts;

interface Castable
{
    /**
     * @param array $arguments
     *
     * @return CastsAttributes
     */
    public static function castUsing(array $arguments): CastsAttributes;
}
