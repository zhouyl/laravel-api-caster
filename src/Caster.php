<?php

declare(strict_types=1);

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
use ReflectionEnum;
use ReflectionException;

/**
 * Data type converter for Entity attributes.
 *
 * This class handles the conversion of raw data values to their appropriate
 * types based on cast definitions. It supports built-in PHP types, custom
 * casters, and various specialized conversions like dates and decimals.
 */
class Caster
{
    /**
     * Default date format for date casting.
     *
     * This format is used when casting values to dates without
     * a specific format specified.
     *
     * @var string
     */
    public static string $dateFormat = 'Y-m-d';

    /**
     * Default datetime format for datetime casting.
     *
     * This format is used when casting values to datetime without
     * a specific format specified.
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
     * Create a new Caster instance.
     *
     * @param null|Entity $entity The entity instance to associate with this caster
     */
    public function __construct(?Entity $entity = null)
    {
        $this->entity = $entity ?? new Entity();
    }

    /**
     * Cast data according to the specified cast type.
     *
     * Converts the given value to the appropriate type based on the cast definition.
     * Supports built-in types (int, string, bool, etc.), custom casters, and enums.
     *
     * @param string $cast  The cast type (e.g., 'int', 'datetime', 'decimal:2', MyEnum::class)
     * @param mixed  $value The value to cast
     *
     * @throws InvalidArgumentException          When cast type is invalid
     * @throws MathException|ReflectionException When decimal casting fails
     *
     * @return mixed The casted value
     *
     * @example
     * $caster->cast('int', '123'); // returns 123
     * $caster->cast('datetime', '2023-01-01'); // returns Carbon instance
     * $caster->cast('decimal:2', '123.456'); // returns '123.46'
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
     * Restore the result after data conversion using cast type.
     *
     * Converts a casted value back to its original representation.
     * This is useful for serialization or when you need the raw value.
     *
     * @param string $cast  The cast type used for conversion
     * @param mixed  $value The casted value to restore
     *
     * @return mixed The restored original value
     *
     * @example
     * $caster->value('datetime', $carbonInstance); // returns formatted string
     * $caster->value('int', 123); // returns 123
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
     * Perform basic data type conversion.
     *
     * Handles the core type casting logic for built-in PHP types and
     * common data transformations like JSON, dates, and collections.
     *
     * @param string $cast  The cast type (e.g., 'int', 'datetime', 'decimal:2')
     * @param mixed  $value The value to cast
     *
     * @throws MathException            When decimal casting fails
     * @throws InvalidArgumentException When JSON parsing fails
     *
     * @return mixed The casted value
     *
     * @internal This method is used internally by the cast() method
     */
    protected function castValue(string $cast, mixed $value): mixed
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

    /**
     * Convert value to float with special handling for infinity and NaN.
     *
     * @param mixed $value The value to convert to float
     *
     * @return float The converted float value
     */
    protected function fromFloat(mixed $value): float
    {
        return match ((string) $value) {
            'Infinity'  => INF,
            '-Infinity' => -INF,
            'NaN'       => NAN,
            default     => (float) $value,
        };
    }

    /**
     * Convert value from JSON string or array-like object.
     *
     * @param mixed $value    The value to convert from JSON
     * @param bool  $asObject Whether to return as object instead of array
     *
     * @return mixed The decoded JSON value
     */
    protected function fromJson(mixed $value, bool $asObject = false): mixed
    {
        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if (is_array($value) || is_object($value)) {
            return (array) $value;
        }

        return json_decode($value ?? '', !$asObject);
    }

    /**
     * Convert value to decimal string with specified precision.
     *
     * @param mixed      $value    The value to convert to decimal
     * @param int|string $decimals Number of decimal places
     *
     * @throws MathException When conversion fails
     *
     * @return string The decimal string representation
     */
    protected function asDecimal(mixed $value, int|string $decimals): string
    {
        try {
            return (string) BigDecimal::of($value)->toScale((int) $decimals, RoundingMode::HALF_UP);
        } catch (BrickMathException $e) {
            throw new MathException('Unable to cast value to a decimal.', $e->getCode(), $e);
        }
    }

    /**
     * Convert value to Carbon date instance (start of day).
     *
     * @param mixed $value The value to convert to date
     *
     * @return Carbon The Carbon date instance
     */
    protected function asDate(mixed $value): Carbon
    {
        return $this->asDateTime($value)->startOfDay();
    }

    /**
     * Convert value to Carbon datetime instance.
     *
     * Supports various input formats including DateTimeInterface objects,
     * timestamps, and formatted date strings.
     *
     * @param mixed $value The value to convert to datetime
     *
     * @throws InvalidArgumentException When the value cannot be parsed as a date
     *
     * @return Carbon The Carbon datetime instance
     */
    protected function asDateTime(mixed $value): Carbon
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

        return false !== $date ? $date : Carbon::parse($value);
    }

    /**
     * Serialize date to string format.
     *
     * @param DateTimeInterface $date The date to serialize
     *
     * @return string The formatted date string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date instanceof DateTimeImmutable
            ? CarbonImmutable::instance($date)->format(static::$dateFormat)
            : Carbon::instance($date)->format(static::$dateFormat);
    }

    /**
     * Serialize datetime to string format.
     *
     * @param DateTimeInterface $date The datetime to serialize
     *
     * @return string The formatted datetime string
     */
    protected function serializeDatetime(DateTimeInterface $date): string
    {
        return $date instanceof DateTimeImmutable
            ? CarbonImmutable::instance($date)->format(static::$datetimeFormat)
            : Carbon::instance($date)->format(static::$datetimeFormat);
    }

    /**
     * Convert value to Unix timestamp.
     *
     * @param mixed $value The value to convert to timestamp
     *
     * @return int The Unix timestamp
     */
    protected function asTimestamp(mixed $value): int
    {
        return $this->asDateTime($value)->getTimestamp();
    }

    /**
     * Get the base cast type from a cast definition.
     *
     * Extracts the base type from cast definitions that may include parameters.
     *
     * @param string $cast The cast definition
     *
     * @return string The base cast type
     */
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

    /**
     * Check if cast type is for date conversion.
     *
     * @param string $cast The cast type to check
     *
     * @return bool True if it's a date cast
     */
    protected function isDateCast(string $cast): bool
    {
        return in_array($cast, ['date', 'immutable_date'], true);
    }

    /**
     * Check if cast type is for datetime conversion.
     *
     * @param string $cast The cast type to check
     *
     * @return bool True if it's a datetime cast
     */
    protected function isDatetimeCast(string $cast): bool
    {
        return in_array($cast, ['date', 'datetime', 'immutable_date', 'immutable_datetime'], true);
    }

    /**
     * Check if cast type is for custom datetime format.
     *
     * @param string $cast The cast type to check
     *
     * @return bool True if it's a custom datetime cast
     */
    protected function isCustomDateTimeCast(string $cast): bool
    {
        return str_starts_with($cast, 'date:') || str_starts_with($cast, 'datetime:');
    }

    /**
     * Check if cast type is for immutable custom datetime format.
     *
     * @param string $cast The cast type to check
     *
     * @return bool True if it's an immutable custom datetime cast
     */
    protected function isImmutableCustomDateTimeCast(string $cast): bool
    {
        return str_starts_with($cast, 'immutable_date:') || str_starts_with($cast, 'immutable_datetime:');
    }

    /**
     * Check if cast type is for decimal conversion.
     *
     * @param string $cast The cast type to check
     *
     * @return bool True if it's a decimal cast
     */
    protected function isDecimalCast(string $cast): bool
    {
        return str_starts_with($cast, 'decimal:');
    }

    /**
     * Check if value matches standard date format (Y-m-d).
     *
     * @param mixed $value The value to check
     *
     * @return bool True if matches standard date format
     */
    protected function isStandardDateFormat(mixed $value): bool
    {
        return is_string($value) && preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value);
    }

    /**
     * Parse the class name from a caster definition.
     *
     * Extracts the class name from cast definitions that may include parameters.
     *
     * @param string $class The caster class definition
     *
     * @return string The class name
     */
    protected function parseCasterClass(string $class): string
    {
        return !str_contains($class, ':') ? $class : explode(':', $class, 2)[0];
    }

    /**
     * Check if cast type uses a custom caster class.
     *
     * @param string $cast The cast type to check
     *
     * @return bool True if it's a class-based cast
     */
    protected function isClassCastable(string $cast): bool
    {
        $castType = $this->parseCasterClass($cast);

        if (in_array($castType, static::$primitiveCastTypes, true)) {
            return false;
        }

        if (class_exists($castType)) {
            return true;
        }

        return false;
    }

    /**
     * Resolve caster class instance from cast definition.
     *
     * @param string $cast The cast definition
     *
     * @return mixed The caster instance
     */
    protected function resolveCasterClass(string $cast): mixed
    {
        $castType = $this->getCastType($cast);

        $arguments = [];

        if (str_contains($castType, ':')) {
            $segments = explode(':', $castType, 2);

            $castType = $segments[0];
            $arguments = array_map(function ($arg) {
                // Try to convert numeric strings to appropriate types
                if (is_numeric($arg)) {
                    return str_contains($arg, '.') ? (float) $arg : (int) $arg;
                }

                return $arg;
            }, explode(',', $segments[1]));
        }

        if (is_subclass_of($castType, Castable::class)) {
            $castType = $castType::castUsing($arguments);
        }

        if (is_object($castType)) {
            return $castType;
        }

        return new $castType(...$arguments);
    }

    /**
     * Get casted value using custom caster.
     *
     * @param string $cast  The cast definition
     * @param mixed  $value The value to cast
     *
     * @return mixed The casted value
     */
    protected function getCastableValue(string $cast, mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

        return $this->resolveCasterClass($cast)->getCastValue($this->entity, $cast, $value);
    }

    /**
     * Convert casted value back to original using custom caster.
     *
     * @param string $cast  The cast definition
     * @param mixed  $value The casted value
     *
     * @return mixed The original value
     */
    protected function fromCastableValue(string $cast, mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

        return $this->resolveCasterClass($cast)->fromCastValue($this->entity, $cast, $value);
    }

    /**
     * Check if cast type is for enum conversion.
     *
     * @param string $cast The cast type to check
     *
     * @return bool True if it's an enum cast
     */
    protected function isEnumCastable(string $cast): bool
    {
        $castType = $this->getCastType($cast);

        if (in_array($castType, static::$primitiveCastTypes, true)) {
            return false;
        }

        return enum_exists($castType);
    }

    /**
     * Get the value from an enum instance.
     *
     * @param mixed $value The enum instance or value
     *
     * @return mixed The enum value
     */
    protected function getEnumValue(mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

        return $value instanceof BackedEnum
            ? $value->value
            : $value->name;
    }

    /**
     * Get enum case from class and value.
     *
     * @param string $enumClass The enum class name
     * @param mixed  $value     The value to convert to enum case
     *
     * @throws ReflectionException
     *
     * @return mixed The enum case
     */
    protected function getEnumCase(string $enumClass, mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

        if (is_subclass_of($enumClass, BackedEnum::class)) {
            // Try to cast the value to the appropriate type for backed enums
            $reflection = new ReflectionEnum($enumClass);
            $backingType = $reflection->getBackingType();

            if (null !== $backingType && 'int' === $backingType->getName()) {
                $value = (int) $value;
            } elseif (null !== $backingType && 'string' === $backingType->getName()) {
                $value = (string) $value;
            }

            return $enumClass::from($value);
        }

        return constant($enumClass.'::'.$value);
    }
}
