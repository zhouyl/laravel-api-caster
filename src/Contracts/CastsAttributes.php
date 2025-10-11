<?php

declare(strict_types=1);

namespace Mellivora\Http\Api\Contracts;

use Mellivora\Http\Api\Entity;

/**
 * @template TGet
 * @template TSet
 */
interface CastsAttributes
{
    /**
     * @param Entity $entity
     * @param string $key
     * @param mixed  $value
     *
     * @return null|TGet
     */
    public function fromCastValue(Entity $entity, string $key, mixed $value);

    /**
     * @param Entity    $entity
     * @param string    $key
     * @param null|TSet $value
     *
     * @return mixed
     */
    public function getCastValue(Entity $entity, string $key, $value): mixed;
}
