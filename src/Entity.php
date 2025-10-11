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
 * 数据实体对象基类.
 */
class Entity implements Arrayable, ArrayAccess, Countable, IteratorAggregate, Jsonable, JsonSerializable, Serializable
{
    /**
     * 原始数据.
     *
     * @var array
     */
    protected array $originAttributes = [];

    /**
     * 转换后的数据.
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * 头信息.
     *
     * @var array
     */
    protected array $meta = [];

    /**
     * 默认数据.
     *
     * @var array
     */
    protected array $defaults = [];

    /**
     * 需要保留的数据字段.
     *
     * @var array|string[]
     */
    protected array $includes = ['*'];

    /**
     * 需要排除的数据字段.
     *
     * @var array
     */
    protected array $excludes = [];

    /**
     * 重命名字段.
     *
     * @var array
     */
    protected array $renames = [];

    /**
     * 需要进行类型转换的数据字段.
     *
     * 例：
     *  [
     *      'status'   => StatusEnum::class,
     *      'log_time' => 'datetime',
     *  ]
     *
     * @var array
     */
    protected array $casts = [];

    /**
     * 需要映射的数据字段.
     *
     * 例：
     *  [
     *      'manufacturer' => ManufacturerEntity::class,
     *      'categories[]' => CategoryEntity::class,
     *  ]
     *
     * @var array
     */
    protected array $mappings = [];

    /**
     * 需要追加的数据字段.
     *
     *  例：['parents']，需要在 Entity 类中提供对应的 getParentsAttribute() 方法
     *
     * @var array
     */
    protected array $appends = [];

    /**
     * 是否使用驼峰式字段命名.
     *
     * @return bool
     */
    protected bool $useCamel = true;

    /**
     * 默认数据转换器.
     *
     * @var Caster
     */
    protected Caster $caster;

    /**
     * 默认的数据类型转换.
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
     * 使用 Response 对象构造响应实体.
     *
     * @param Response $response
     *
     * @return static
     */
    public static function from(Response $response): static
    {
        return new static($response->data(), $response->meta());
    }

    /**
     * 将数据转换为 Collection[Entity] 结构.
     *
     * @param iterable $items
     * @param array    $meta
     *
     * @return Collection
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
     * 构造方法，传入 Entity 实体的 array 数据.
     *
     * @param iterable $attributes
     * @param array    $meta
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
     * 获取请求头信息.
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
     * 获取原始数据.
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
     * 判断是否为空数据.
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
     * 获取所有的主键.
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->attributes);
    }

    /**
     * 获取所有的值
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
     * 获取实体属性.
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
     * 设置实体属性.
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
     * 判断属性是否存在.
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
     * 将传入的数据转换为数组.
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
     * 数据初始化.
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
     * 合并 caster 转换数据.
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
     * 映射 Mappings 实体.
     *
     * @param string   $class
     * @param iterable $attributes
     *
     * @return self
     */
    protected function mappingEntity(string $class, iterable $attributes): self
    {
        if (!is_a($class, self::class, true)) {
            throw new UnexpectedValueException("Mapping class '$class' must instance of " . self::class);
        }

        return new $class($attributes, $this->meta());
    }

    /**
     * 映射 Mappings 实体集合.
     *
     * @param string   $class
     * @param iterable $attributes
     *
     * @return Collection
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
     * 获取所有的属性修改器.
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
     * 转换为驼峰式数据格式.
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
     * 判断是否为 include 字段.
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
     * 验证输入数据的安全性.
     *
     * @param iterable $attributes
     * @param array    $meta
     *
     * @throws \InvalidArgumentException
     */
    protected function validateInput(iterable $attributes, array $meta): void
    {
        // 检查递归深度以防止栈溢出
        if ($this->getRecursionDepth($attributes) > 100) {
            throw new \InvalidArgumentException('Input data exceeds maximum recursion depth');
        }

        // 检查数组大小以防止内存耗尽
        if ($this->getArraySize($attributes) > 10000) {
            throw new \InvalidArgumentException('Input data exceeds maximum size limit');
        }

        // 验证 meta 数据
        if (count($meta) > 1000) {
            throw new \InvalidArgumentException('Meta data exceeds maximum size limit');
        }
    }

    /**
     * 获取数组的递归深度.
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
     * 获取数组的大小.
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
     * 获取属性值数据.
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
