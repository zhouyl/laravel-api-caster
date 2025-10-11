<?php

declare(strict_types=1);

namespace Mellivora\Http\Api\Tests\MockLib;

use Mellivora\Http\Api\Contracts\Castable;
use Mellivora\Http\Api\Contracts\CastsAttributes;
use Mellivora\Http\Api\Entity;

class Message implements Castable
{
    /**
     * @inheritDoc
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class () implements CastsAttributes {
            public function getCastValue(Entity $entity, string $key, $value): array
            {
                return [$key => $value];
            }

            public function fromCastValue(Entity $entity, string $key, mixed $value)
            {
                return $value[$key];
            }
        };
    }
}
