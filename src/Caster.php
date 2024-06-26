<?php

namespace Mellivora\Http\Api;

use BackedEnum;
use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException as BrickMathException;
use Brick\Math\RoundingMode;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Exceptions\MathException;
use InvalidArgumentException;
use Mellivora\Http\Api\Contracts\Castable;

class Caster
{
    /**
     * 默认日期格式
     *
     * @var string
     */
    public static string $dateFormat = 'Y-m-d';

    /**
     * 默认时间格式
     *
     * @var string
     */
    public static string $datetimeFormat = 'Y-m-d H:i:s';

    /**
     * @var array|string[]
     */
    protected static array $primitiveCastTypes = [
        'array',
        'bool',
        'boolean',
        'collection',
        'custom_datetime',
        'date',
        'datetime',
        'decimal',
        'double',
        'float',
        'hashed',
        'immutable_date',
        'immutable_datetime',
        'immutable_custom_datetime',
        'int',
        'integer',
        'json',
        'object',
        'real',
        'string',
        'timestamp',
    ];

    /**
     * @var Entity
     */
    protected Entity $entity;

    /**
     * @var array
     */
    protected array $attributes;

    /**
     * @param null|Entity $entity
     */
    public function __construct(Entity $entity = null)
    {
        $this->entity = $entity ?? new Entity();
    }

    /**
     * 根据 $cast 类型转换数据
     *
     * @param string $cast
     * @param mixed  $value
     *
     * @return mixed
     */
    public function cast(string $cast, mixed $value): mixed
    {
        $value = $this->castValue($cast, $value);

        if ($this->isEnumCastable($cast)) {
            $value = $this->getEnumCase($cast, $value);
        } elseif ($this->isClassCastable($cast)) {
            $value = $this->getCastableValue($cast, $value);
        }

        return $value;
    }

    /**
     * 将使用 $cast 数据转换后的结果进行还原
     *
     * @param string $cast
     * @param mixed  $value
     *
     * @return mixed
     */
    public function value(string $cast, mixed $value): mixed
    {
        $value = $this->castValue($cast, $value);

        if ($this->isDateCast($cast)) {
            $value = $this->serializeDate($value);
        } elseif ($this->isDatetimeCast($cast)) {
            $value = $this->serializeDatetime($value);
        }

        if ($this->isCustomDateTimeCast($cast) || $this->isImmutableCustomDateTimeCast($cast)) {
            $value = $value->format(explode(':', $cast, 2)[1]);
        }

        if ($this->isEnumCastable($cast)) {
            $value = $this->getEnumValue($value);
        } elseif ($this->isClassCastable($cast)) {
            $value = $this->fromCastableValue($cast, $value);
        }

        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        return $value;
    }

    /**
     * 进行基础的数据类型转换
     *
     * @param string $cast
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function castValue(string $cast, $value): mixed
    {
        return match ($this->getCastType($cast)) {
            'int', 'integer' => (int) $value,
            'real', 'float', 'double' => $this->fromFloat($value),
            'decimal' => $this->asDecimal($value, explode(':', $cast, 2)[1] ?? 0),
            'string'  => (string) $value,
            'bool', 'boolean' => (bool) $value,
            'object' => $this->fromJson($value, true),
            'array', 'json' => $this->fromJson($value),
            'collection' => new Collection($this->fromJson($value)),
            'date'       => $this->asDate($value),
            'datetime', 'custom_datetime' => $this->asDateTime($value),
            'immutable_date' => $this->asDate($value)->toImmutable(),
            'immutable_custom_datetime', 'immutable_datetime' => $this->asDateTime($value)->toImmutable(),
            'timestamp' => $this->asTimestamp($value),
            default     => $value,
        };
    }

    protected function fromFloat($value): float
    {
        return match ((string) $value) {
            'Infinity'  => INF,
            '-Infinity' => -INF,
            'NaN'       => NAN,
            default     => (float) $value,
        };
    }

    protected function fromJson($value, $asObject = false)
    {
        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if (is_array($value) || is_object($value)) {
            return (array) $value;
        }

        return json_decode($value ?? '', !$asObject);
    }

    protected function asDecimal($value, $decimals): string
    {
        try {
            return (string) BigDecimal::of($value)->toScale($decimals, RoundingMode::HALF_UP);
        } catch (BrickMathException $e) {
            throw new MathException('Unable to cast value to a decimal.', $e->getCode(), $e);
        }
    }

    protected function asDate($value): Carbon
    {
        return $this->asDateTime($value)->startOfDay();
    }

    protected function asDateTime($value)
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::parse(
                $value->format('Y-m-d H:i:s.u'),
                $value->getTimezone()
            );
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        if ($this->isStandardDateFormat($value)) {
            return Carbon::createFromFormat(static::$dateFormat, $value)->startOfDay();
        }

        try {
            $date = Carbon::createFromFormat(static::$datetimeFormat, $value);
        } catch (InvalidArgumentException) {
            $date = false;
        }

        return $date ?: Carbon::parse($value);
    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date instanceof DateTimeImmutable ?
            CarbonImmutable::instance($date)->format(static::$dateFormat) :
            Carbon::instance($date)->format(static::$dateFormat);
    }

    protected function serializeDatetime(DateTimeInterface $date): string
    {
        return $date instanceof DateTimeImmutable ?
            CarbonImmutable::instance($date)->format(static::$datetimeFormat) :
            Carbon::instance($date)->format(static::$datetimeFormat);
    }

    protected function asTimestamp($value): int
    {
        return $this->asDateTime($value)->getTimestamp();
    }

    protected function getCastType(string $cast): string
    {
        if ($this->isCustomDateTimeCast($cast)) {
            return 'custom_datetime';
        }

        if ($this->isImmutableCustomDateTimeCast($cast)) {
            return 'immutable_custom_datetime';
        }

        if ($this->isDecimalCast($cast)) {
            return 'decimal';
        }

        if (class_exists($cast)) {
            return $cast;
        }

        return trim(strtolower($cast));

    }

    protected function isDateCast(string $cast): bool
    {
        return in_array($cast, ['date', 'immutable_date']);
    }

    protected function isDatetimeCast(string $cast): bool
    {
        return in_array($cast, ['date', 'datetime', 'immutable_date', 'immutable_datetime']);
    }

    protected function isCustomDateTimeCast(string $cast): bool
    {
        return str_starts_with($cast, 'date:') || str_starts_with($cast, 'datetime:');
    }

    protected function isImmutableCustomDateTimeCast(string $cast): bool
    {
        return str_starts_with($cast, 'immutable_date:') || str_starts_with($cast, 'immutable_datetime:');
    }

    protected function isDecimalCast(string $cast): bool
    {
        return str_starts_with($cast, 'decimal:');
    }

    protected function isStandardDateFormat($value): bool
    {
        return (bool) preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value);
    }

    protected function parseCasterClass(string $class): string
    {
        return !str_contains($class, ':') ? $class : explode(':', $class, 2)[0];
    }

    protected function isClassCastable(string $cast): bool
    {
        $castType = $this->parseCasterClass($cast);

        if (in_array($castType, static::$primitiveCastTypes)) {
            return false;
        }

        if (class_exists($castType)) {
            return true;
        }

        return false;
    }

    protected function resolveCasterClass($cast): mixed
    {
        $castType = $this->getCastType($cast);

        $arguments = [];

        if (is_string($castType) && str_contains($castType, ':')) {
            $segments = explode(':', $castType, 2);

            $castType  = $segments[0];
            $arguments = explode(',', $segments[1]);
        }

        if (is_subclass_of($castType, Castable::class)) {
            $castType = $castType::castUsing($arguments);
        }

        if (is_object($castType)) {
            return $castType;
        }

        return new $castType(...$arguments);
    }

    protected function getCastableValue($cast, $value)
    {
        if (null === $value) {
            return null;
        }

        return $this->resolveCasterClass($cast)->getCastValue($this->entity, $cast, $value);
    }

    protected function fromCastableValue($cast, $value)
    {
        if (null === $value) {
            return null;
        }

        return $this->resolveCasterClass($cast)->fromCastValue($this->entity, $cast, $value);
    }

    protected function isEnumCastable(string $cast): bool
    {
        $castType = $this->getCastType($cast);

        if (in_array($castType, static::$primitiveCastTypes)) {
            return false;
        }

        return enum_exists($castType);
    }

    protected function getEnumValue($value)
    {
        if (null === $value) {
            return null;
        }

        return $value instanceof BackedEnum
            ? $value->value
            : $value->name;
    }

    protected function getEnumCase($enumClass, $value)
    {
        if (null === $value) {
            return null;
        }

        return is_subclass_of($enumClass, BackedEnum::class)
            ? $enumClass::from($value)
            : constant($enumClass.'::'.$value);
    }
}
