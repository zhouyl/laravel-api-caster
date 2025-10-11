<?php

declare(strict_types=1);

namespace Mellivora\Http\Api;

use ArrayAccess;
use ArrayIterator;
use ArrayObject;
use BackedEnum;
use Carbon\Carbon;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use IteratorAggregate;
use JsonSerializable;
use Serializable;
use Traversable;
use UnexpectedValueException;

/**
 * Base class for data entity objects.
 *
 * This class provides a Laravel Eloquent-like interface for working with API response data.
 * It supports type casting, field mapping, data transformation, and various array-like operations.
 *
 * @package Mellivora\Http\Api
 * @author zhouyl <81438567@qq.com>
 * @version 2.0.0
 * @since 1.0.0
 *
 * @implements ArrayAccess<string, mixed>
 * @implements IteratorAggregate<string, mixed>
 *
 * @property-read array<string, mixed> $attributes The converted attributes
 * @property-read array<string, mixed> $originAttributes The original attributes
 * @property-read array<string, mixed> $meta The meta information
 *
 * @method mixed __get(string $name) Get an attribute value
 * @method void __set(string $name, mixed $value) Set an attribute value
 * @method bool __isset(string $name) Check if an attribute exists
 * @method void __unset(string $name) Unset an attribute
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @psalm-consistent-constructor
 * @psalm-consistent-templates
 */
class Entity implements Arrayable, ArrayAccess, Countable, IteratorAggregate, Jsonable, JsonSerializable, Serializable
{
    /**
     * Original data before any transformations.
     *
     * This array contains the raw data as it was passed to the constructor,
     * before any casting, mapping, or other transformations were applied.
     *
     * @var array<string, mixed>
     */
    protected array $originAttributes = [];

    /**
     * Converted data after all transformations.
     *
     * This array contains the final data after applying casts, mappings,
     * renames, and other transformations.
     *
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * Meta information associated with the entity.
     *
     * This typically contains metadata from API responses such as
     * pagination info, timestamps, or other contextual data.
     *
     * @var array<string, mixed>
     */
    protected array $meta = [];

    /**
     * Default values for attributes.
     *
     * These values will be merged with the input data during construction.
     *
     * @var array<string, mixed>
     */
    protected array $defaults = [];

    /**
     * Data fields to be retained during processing.
     *
     * Use ['*'] to include all fields, or specify an array of field names
     * to include only those fields. This is processed before excludes.
     *
     * @var array<string>
     * @example ['*'] Include all fields
     * @example ['id', 'name', 'email'] Include only specific fields
     */
    protected array $includes = ['*'];

    /**
     * Data fields to be excluded from processing.
     *
     * Fields listed here will be removed from the final attributes,
     * even if they are included in the includes array.
     *
     * @var array<string>
     * @example ['password', 'secret_key'] Exclude sensitive fields
     */
    protected array $excludes = [];

    /**
     * Field renaming mapping.
     *
     * Maps original field names to new field names. The key is the original
     * name and the value is the new name.
     *
     * @var array<string, string>
     * @example ['user_id' => 'id', 'full_name' => 'name']
     */
    protected array $renames = [];

    /**
     * Data fields that need type conversion.
     *
     * Defines how specific fields should be cast to different types.
     * Supports built-in types (int, string, bool, etc.) and custom casters.
     *
     * @var array<string, string|class-string>
     * @example [
     *     'id' => 'int',
     *     'status' => StatusEnum::class,
     *     'created_at' => 'datetime',
     *     'settings' => 'json',
     *     'score' => 'decimal:2'
     * ]
     */
    protected array $casts = [];

    /**
     * Data fields that need entity mapping.
     *
     * Maps nested data to Entity instances. Use [] suffix for arrays.
     * The key is the field name and the value is the Entity class.
     *
     * @var array<string, class-string<Entity>>
     * @example [
     *     'user' => UserEntity::class,
     *     'categories[]' => CategoryEntity::class,
     *     'meta.author' => AuthorEntity::class
     * ]
     */
    protected array $mappings = [];

    /**
     * Data fields to be appended as computed attributes.
     *
     * These fields will be added to the entity using accessor methods.
     * Each field requires a corresponding getFieldNameAttribute() method.
     *
     * @var array<string>
     * @example ['full_name', 'display_name', 'is_admin']
     */
    protected array $appends = [];

    /**
     * Whether to use camelCase field naming.
     *
     * @return bool
     */
    protected bool $useCamel = true;

    /**
     * Default data converter.
     *
     * @var Caster
     */
    protected Caster $caster;

    /**
     * Default data type conversions.
     *
     * @var array|string[]
     */
    protected array $defaultCasts = [];

    /**
     * @var null|array
     */
    protected ?array $bootedCasts = null;

    /**
     * Cache for computed attributes.
     *
     * @var array
     */
    protected array $computedCache = [];

    /**
     * Cache for includes/excludes checks.
     *
     * @var array
     */
    protected array $includeCache = [];

    /**
     * 使用 Response 对象构造响应实体集合.
     *
     * @param Response $response
     *
     * @return Collection
     */
    public static function collectionResponse(Response $response): Collection
    {
        return static::collection($response->data(), $response->meta());
    }

    /**
     * Create Entity from Response object.
     *
     * Factory method that creates a new entity instance from an API response.
     * Extracts data and meta information from the response automatically.
     *
     * @param Response $response The API response object
     *
     * @return static New entity instance
     *
     * @example
     * $response = new Response($httpResponse);
     * $user = UserEntity::from($response);
     */
    public static function from(Response $response): static
    {
        return new static($response->data(), $response->meta());
    }

    /**
     * Convert data to Collection of Entity instances.
     *
     * Creates a Laravel Collection containing Entity instances from an array
     * of data items. Each item will be converted to an entity of the calling class.
     *
     * @param iterable<mixed> $items Array of data items to convert
     * @param array<string, mixed> $meta Optional meta information for all entities
     *
     * @return Collection<int, static> Collection of entity instances
     *
     * @example
     * $users = UserEntity::collection([
     *     ['id' => 1, 'name' => 'John'],
     *     ['id' => 2, 'name' => 'Jane']
     * ]);
     */
    public static function collection(iterable $items, array $meta = []): Collection
    {
        $collection = new Collection();

        foreach ($items as $item) {
            if (is_array($item)) {
                $item = new static($item, $meta);
            }

            if (!$item instanceof static) {
                throw new UnexpectedValueException('Expected instance of ' . static::class);
            }

            $collection->push($item);
        }

        return $collection;
    }

    /**
     * Constructor method, creates a new Entity instance.
     *
     * Initializes the entity with the provided attributes and meta data.
     * The attributes will be processed according to the entity's configuration
     * including casts, mappings, renames, includes, and excludes.
     *
     * @param iterable<string, mixed> $attributes The entity attributes
     * @param array<string, mixed> $meta Optional meta information
     *
     * @throws \InvalidArgumentException When input data exceeds safety limits
     *
     * @example
     * $entity = new Entity(['id' => 1, 'name' => 'John']);
     * $entity = new Entity($apiData, ['total' => 100, 'page' => 1]);
     */
    public function __construct(iterable $attributes = [], array $meta = [])
    {
        $this->validateInput($attributes, $meta);

        $this->caster = new Caster($this);
        $this->meta = $this->asCamels($meta);
        $this->originAttributes = $this->asCamels($this->getArrayableItems($attributes) + $this->defaults);

        $this->boot();
    }

    /**
     * {@inheritDoc}
     */
    public function __get(string $name): mixed
    {
        return $this->offsetGet($name);
    }

    /**
     * {@inheritDoc}
     */
    public function __set(string $name, mixed $value): void
    {
        $this->offsetSet($name, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function __isset(string $name): bool
    {
        return $this->offsetExists($name);
    }

    /**
     * {@inheritDoc}
     */
    public function __unset(string $name): void
    {
        $this->offsetUnset($name);
    }

    /**
     * {@inheritDoc}
     */
    public function __serialize(): array
    {
        return [$this->originAttributes, $this->meta];
    }

    /**
     * {@inheritDoc}
     */
    public function __unserialize(array $data): void
    {
        [
            $this->originAttributes,
            $this->meta,
        ] = $data;

        $this->caster = new Caster($this);
        $this->boot();
    }

    /**
     * @codeCoverageIgnore
     *
     * {@inheritDoc}
     */
    public function __debugInfo(): array
    {
        return $this->attributes;
    }

    /**
     * Get request header information.
     *
     * @param null|string $key
     * @param null|mixed  $default
     *
     * @return null|array|mixed
     */
    public function meta(string $key = null, mixed $default = null): mixed
    {
        return $key ? data_get($this->meta, $key, $default) : $this->meta;
    }

    /**
     * Get original data.
     *
     * @param null|string $key
     * @param null|mixed  $default
     *
     * @return null|array|mixed
     */
    public function origin(string $key = null, mixed $default = null): mixed
    {
        return $key ? data_get($this->originAttributes, $key, $default) : $this->originAttributes;
    }

    /**
     * Check if data is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->attributes);
    }

    /**
     * 初始化方法，继承类可通过实例该方法，完成对数据的初始化处理.
     *
     * @param array $attributes
     *
     * @return array
     */
    public function initialize(array $attributes): array
    {
        return $attributes;
    }

    /**
     * 复制一个当前对象
     *
     * @return static
     */
    public function copy(): static
    {
        return clone $this;
    }

    /**
     * Get all keys.
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->attributes);
    }

    /**
     * Get all values.
     *
     * @return array
     */
    public function values(): array
    {
        return array_values($this->attributes);
    }

    /**
     * 遗弃掉某些字段数据(当前实例).
     *
     * @param array $keys
     *
     * @return static
     */
    public function forget(array $keys): static
    {
        foreach ($keys as $key) {
            $this->offsetUnset($key);
        }

        return $this;
    }

    /**
     * 使用指定的字段，重新创建一个新的 Entity 实例.
     *
     * @param array $keys
     *
     * @return static
     */
    public function only(array $keys): static
    {
        $array = [];

        foreach ($keys as $key) {
            if ($this->offsetExists($key)) {
                data_set($array, $key, $this->offsetGet($key));
            }
        }

        return new static($array, $this->meta());
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        $array = [];

        foreach ($this->attributes as $key => $value) {
            if (isset($this->getCasts()[$key])) {
                $array[$key] = $this->caster->value($this->getCasts()[$key], $value);
            } else {
                $array[$key] = $this->getEntityValue($value);
            }
        }

        return $array;
    }

    /**
     * {@inheritDoc}
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet(mixed $offset): mixed
    {
        return data_get($this->attributes, $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists(mixed $offset): bool
    {
        $attributes = $this->attributes;

        foreach (explode('.', (string) $offset) as $segment) {
            if (!isset($attributes[$segment])) {
                return false;
            }

            $attributes = $attributes[$segment];
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        data_set($this->attributes, $offset, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset(mixed $offset): void
    {
        data_forget($this->attributes, $offset);
    }

    /**
     * @codeCoverageIgnore
     *
     * {@inheritDoc}
     */
    public function serialize(): ?string
    {
        return serialize($this->__serialize());
    }

    /**
     * @codeCoverageIgnore
     *
     * {@inheritDoc}
     */
    public function unserialize(string $serialized): void
    {
        $this->__unserialize(unserialize($serialized));
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): Traversable|ArrayIterator
    {
        return new ArrayIterator($this->attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Get entity attribute.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function set(string $key, mixed $default = null): mixed
    {
        return $this->offsetGet($key) ?? $default;
    }

    /**
     * Set entity attribute.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return static
     */
    public function get(string $key, mixed $value): static
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * Check if attribute exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * 移除属性.
     *
     * @param string $key
     *
     * @return static
     */
    public function remove(string $key): static
    {
        $this->offsetUnset($key);

        return $this;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function getEntityValue(mixed $value): mixed
    {
        // @codeCoverageIgnoreStart
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof Carbon) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if ($value instanceof Jsonable) {
            return $value->toJson();
        }

        if ($value instanceof JsonSerializable) {
            return $value->jsonSerialize();
        }
        // @codeCoverageIgnoreEnd

        if (is_iterable($value)) {
            $values = [];

            foreach ($value as $key => $item) {
                $values[$key] = $this->getEntityValue($item);
            }

            return $values;
        }

        return $value;
    }

    /**
     * Convert input data to array.
     *
     * @param mixed $items
     *
     * @return array
     */
    protected function getArrayableItems(mixed $items): array
    {
        if (is_array($items)) {
            return $items;
        }

        if ($items instanceof BackedEnum) {
            return [$items->value];
        }

        if ($items instanceof Carbon) {
            return [$items->format('Y-m-d H:i:s')];
        }

        if ($items instanceof Arrayable) {
            return $items->toArray();
        }

        if ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true) ?: [];
        }

        if ($items instanceof JsonSerializable) {
            return (array) $items->jsonSerialize();
        }

        if (method_exists($items, 'toArray')) {
            return $items->toArray();
        }

        if (method_exists($items, 'toJson')) {
            return json_decode($items->toJson(), true) ?: [];
        }

        if ($items instanceof ArrayObject) {
            return $items->getArrayCopy();
        }

        if ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array) $items;
    }

    /**
     * Initialize entity data.
     *
     * Processes the original attributes through various transformations
     * including casts, mappings, and appends to create the final attributes.
     */
    protected function boot(): void
    {
        $this->attributes = [];

        // 合并需要加载的数据
        foreach ($this->originAttributes as $key => $value) {
            if (isset($this->renames[$key])) {
                $key = $this->renames[$key];
            }

            if (!$this->canInclude($key)) {
                continue;
            }

            $this->attributes[$key] = $value;
        }

        $this->attributes = $this->initialize($this->attributes);
        $this->attributes = $this->bootCasts($this->attributes);
        $this->attributes = $this->bootMappings($this->attributes);
        $this->attributes = $this->bootAppends($this->attributes);
    }

    /**
     * Merge caster converted data.
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function bootCasts(array $attributes): array
    {
        foreach ($attributes as $key => &$value) {
            if (isset($this->getCasts()[$key])) {
                $value = $this->caster->cast($this->getCasts()[$key], $value);
            }
        }

        return $attributes;
    }

    /**
     * Create a mapped entity instance.
     *
     * Creates an instance of the specified entity class with the given attributes.
     * The class must be a subclass of Entity.
     *
     * @param class-string<Entity> $class The entity class name
     * @param iterable<string, mixed> $attributes The attributes for the entity
     *
     * @return Entity The created entity instance
     *
     * @throws UnexpectedValueException When the class is not a valid Entity subclass
     */
    protected function mappingEntity(string $class, iterable $attributes): Entity
    {
        if (!is_a($class, self::class, true)) {
            throw new UnexpectedValueException("Mapping class '$class' must instance of " . self::class);
        }

        return new $class($attributes, $this->meta());
    }

    /**
     * Create a collection of mapped entity instances.
     *
     * Creates a Collection containing instances of the specified entity class,
     * one for each item in the attributes iterable.
     *
     * @param class-string<Entity> $class The entity class name
     * @param iterable<mixed> $attributes The attributes for each entity
     *
     * @return Collection<int, Entity> Collection of entity instances
     *
     * @throws UnexpectedValueException When the class is not a valid Entity subclass
     */
    protected function mappingCollection(string $class, iterable $attributes): Collection
    {
        if (!is_a($class, self::class, true)) {
            throw new UnexpectedValueException("Mapping class '$class' must instance of " . self::class);
        }

        return $class::collection($attributes, $this->meta());
    }

    /**
     * 合并字段映射后的数据.
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function bootMappings(array $attributes): array
    {
        if (isset($this->mappings['*'])) {
            return array_map(fn ($item) => $this->mappingEntity($this->mappings['*'], $item), $attributes);
        }

        if (isset($this->mappings['*[]'])) {
            return array_map(fn ($item) => $this->mappingCollection($this->mappings['*[]'], $item), $attributes);
        }

        foreach ($this->mappings as $key => $class) {
            $isArray = false;

            if (Str::endsWith($key, '[]')) {
                $key = substr($key, 0, -2);
                $isArray = true;
            }

            if (isset($attributes[$key])) {
                $attributes[$key] = $isArray
                    ? $this->mappingCollection($class, $attributes[$key])
                    : $this->mappingEntity($class, $attributes[$key]);
            }
        }

        return $attributes;
    }

    /**
     * 合并追加后的数据.
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function bootAppends(array $attributes): array
    {
        foreach (array_merge(array_keys($attributes), $this->appends) as $key) {
            $attributes[$key] = $this->getAttributeValue($key, $attributes[$key] ?? null);
        }

        return $attributes;
    }

    /**
     * Get all attribute modifiers.
     *
     * @return string[]
     */
    protected function getCasts(): array
    {
        if (null === $this->bootedCasts) {
            $this->bootedCasts = $this->casts + $this->defaultCasts;

            if ($this->useCamel) {
                $this->bootedCasts = array_combine(
                    array_map(fn ($key) => Str::camel($key), array_keys($this->bootedCasts)),
                    array_values($this->bootedCasts)
                );
            }
        }

        return $this->bootedCasts;
    }

    /**
     * Convert to camelCase data format.
     *
     * @param array $data
     *
     * @return array
     */
    protected function asCamels(array $data): array
    {
        if (!$this->useCamel) {
            return $data;
        }

        $array = [];

        foreach ($data as $key => $value) {
            $array[Str::camel($key)] = is_array($value) ? $this->asCamels($value) : $value;
        }

        return $array;
    }

    /**
     * Check if field can be included.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function canInclude(string $key): bool
    {
        if (!isset($this->includeCache[$key])) {
            $this->includeCache[$key] = (in_array('*', $this->includes) || in_array($key, $this->includes))
                && !in_array($key, $this->excludes);
        }

        return $this->includeCache[$key];
    }

    /**
     * Validate input data security.
     *
     * @param iterable $attributes
     * @param array    $meta
     *
     * @throws \InvalidArgumentException
     */
    protected function validateInput(iterable $attributes, array $meta): void
    {
        // Check recursion depth to prevent stack overflow
        if ($this->getRecursionDepth($attributes) > 100) {
            throw new \InvalidArgumentException('Input data exceeds maximum recursion depth');
        }

        // Check array size to prevent memory exhaustion
        if ($this->getArraySize($attributes) > 10000) {
            throw new \InvalidArgumentException('Input data exceeds maximum size limit');
        }

        // Validate meta data
        if (count($meta) > 1000) {
            throw new \InvalidArgumentException('Meta data exceeds maximum size limit');
        }
    }

    /**
     * Get array recursion depth.
     *
     * @param mixed $data
     * @param int   $depth
     *
     * @return int
     */
    protected function getRecursionDepth(mixed $data, int $depth = 0): int
    {
        if (!is_iterable($data)) {
            return $depth;
        }

        $maxDepth = $depth;
        foreach ($data as $item) {
            if (is_iterable($item)) {
                $maxDepth = max($maxDepth, $this->getRecursionDepth($item, $depth + 1));
            }
        }

        return $maxDepth;
    }

    /**
     * Get array size.
     *
     * @param mixed $data
     *
     * @return int
     */
    protected function getArraySize(mixed $data): int
    {
        if (!is_iterable($data)) {
            return 1;
        }

        $size = 0;
        foreach ($data as $item) {
            $size += is_iterable($item) ? $this->getArraySize($item) : 1;
        }

        return $size;
    }

    /**
     * Get attribute value data.
     *
     * @param string     $key
     * @param null|mixed $default
     *
     * @return mixed
     */
    protected function getAttributeValue(string $key, mixed $default = null): mixed
    {
        $method = 'get' . Str::studly($key) . 'Attribute';

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        return $this->attributes[$key] ?? $default;
    }
}
