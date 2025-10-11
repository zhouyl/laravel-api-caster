<?php

declare(strict_types=1);

namespace Mellivora\Http\Api\Tests\MockLib;

use Mellivora\Http\Api\Contracts\CastsAttributes;
use Mellivora\Http\Api\Entity;

class SplitterCastAttribute implements CastsAttributes
{
    public static string $delimiter = ',';

    public function __construct(public int $id)
    {
    }

    public function getCastValue(Entity $entity, string $key, mixed $value): array
    {
        $splits = preg_split('/[' . preg_quote(static::$delimiter) . ']/', $value);
        $values = [];

        foreach ($splits as $value) {
            $value = trim($value);

            if (is_numeric($value)) {
                $values[] = (int) $value == $value ? (int) $value : (float) $value;
            } elseif (!empty($value)) {
                $values[] = $value;
            }
        }

        return $values;
    }

    public function fromCastValue(Entity $entity, string $key, mixed $value): string
    {
        return implode(static::$delimiter, $value);
    }
}
