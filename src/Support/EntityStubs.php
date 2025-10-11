<?php

declare(strict_types=1);

namespace Mellivora\Http\Api\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mellivora\Http\Api\Entity;
use Mellivora\Http\Api\Response;

/**
 * IDE support stubs for Entity class.
 *
 * This file provides enhanced IDE support with type hints and method signatures
 * for better auto-completion and static analysis.
 *
 * @internal This class is for IDE support only and should not be used directly
 */
abstract class EntityStubs
{
    /**
     * Create entity from Response.
     *
     * @param Response $response
     *
     * @return static
     */
    public static function from(Response $response): static
    {
    }

    /**
     * Create collection of entities.
     *
     * @param iterable<mixed>      $items
     * @param array<string, mixed> $meta
     *
     * @return Collection<int, static>
     */
    public static function collection(iterable $items, array $meta = []): Collection
    {
    }

    /**
     * Create collection from Response.
     *
     * @param Response $response
     *
     * @return Collection<int, static>
     */
    public static function collectionResponse(Response $response): Collection
    {
    }

    /**
     * Create a new entity instance.
     *
     * @param iterable<string, mixed> $attributes
     * @param array<string, mixed>    $meta
     */
    public function __construct(iterable $attributes = [], array $meta = [])
    {
    }

    /**
     * Get attribute value.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getAttribute(string $key, mixed $default = null): mixed
    {
    }

    /**
     * Set attribute value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return static
     */
    public function setAttribute(string $key, mixed $value): static
    {
    }

    /**
     * Check if attribute exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasAttribute(string $key): bool
    {
    }

    /**
     * Get meta information.
     *
     * @param null|string $key
     * @param mixed       $default
     *
     * @return mixed
     */
    public function meta(?string $key = null, mixed $default = null): mixed
    {
    }

    /**
     * Get original data.
     *
     * @param null|string $key
     * @param mixed       $default
     *
     * @return mixed
     */
    public function origin(?string $key = null, mixed $default = null): mixed
    {
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
    }

    /**
     * Convert to JSON.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson(int $options = 0): string
    {
    }

    /**
     * Get all keys.
     *
     * @return array<string>
     */
    public function keys(): array
    {
    }

    /**
     * Get all values.
     *
     * @return array<mixed>
     */
    public function values(): array
    {
    }

    /**
     * Check if empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
    }

    /**
     * Create a copy.
     *
     * @return static
     */
    public function copy(): static
    {
    }

    /**
     * Count attributes.
     *
     * @return int
     */
    public function count(): int
    {
    }
}

/**
 * Common entity attribute types for IDE support.
 *
 * @internal This class is for IDE support only
 */
abstract class CommonEntityTypes
{
    /** @var int */
    public int $id;

    /** @var string */
    public string $name;

    /** @var string */
    public string $email;

    /** @var Carbon */
    public Carbon $createdAt;

    /** @var Carbon */
    public Carbon $updatedAt;

    /** @var array<string, mixed> */
    public array $settings;

    /** @var Collection<int, mixed> */
    public Collection $items;

    /** @var bool */
    public bool $isActive;

    /** @var float */
    public float $score;

    /** @var string */
    public string $status;
}

/**
 * Cast type definitions for IDE support.
 *
 * @internal This class is for IDE support only
 */
abstract class CastTypes
{
    public const INT = 'int';

    public const INTEGER = 'integer';

    public const FLOAT = 'float';

    public const DOUBLE = 'double';

    public const REAL = 'real';

    public const STRING = 'string';

    public const BOOL = 'bool';

    public const BOOLEAN = 'boolean';

    public const ARRAY = 'array';

    public const JSON = 'json';

    public const OBJECT = 'object';

    public const COLLECTION = 'collection';

    public const DATE = 'date';

    public const DATETIME = 'datetime';

    public const TIMESTAMP = 'timestamp';

    /** @var string Decimal with 2 places */
    public const DECIMAL_2 = 'decimal:2';

    /** @var string Custom date format */
    public const DATE_CUSTOM = 'date:Y-m-d';

    /** @var string Custom datetime format */
    public const DATETIME_CUSTOM = 'datetime:Y-m-d H:i:s';
}

/**
 * Response structure types for IDE support.
 *
 * @internal This class is for IDE support only
 */
abstract class ResponseTypes
{
    /** @var int Response code */
    public int $code;

    /** @var string Response message */
    public string $message;

    /** @var array<string, mixed> Response data */
    public array $data;

    /** @var array<string, mixed> Response meta */
    public array $meta;
}
