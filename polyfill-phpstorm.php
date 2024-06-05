<?php

if (!interface_exists('UnitEnum')) {
    interface UnitEnum
    {
        public static function cases(): array;
    }
}

if (!interface_exists('BackedEnum')) {
    /**
     * @property-read string $name
     * @property-read mixed $value
     */
    interface BackedEnum extends UnitEnum
    {
        /**
         * @param int|string $value
         *
         * @throws ValueError
         *
         * @return static
         */
        public static function from($value): self;

        /**
         * @param int|string $value
         *
         * @return ?static
         */
        public static function tryFrom($value): ?self;
    }
}

if (!class_exists('ReflectionAttribute')) {
    class ReflectionAttribute implements Reflector
    {
        public const IS_INSTANCEOF = 2;

        public static function export()
        {
        }

        private function __construct()
        {
        }

        private function __clone(): void
        {
        }

        public function __toString(): string
        {
        }

        public function getName(): string
        {
        }

        public function getTarget(): int
        {
        }

        public function isRepeated(): bool
        {
        }

        public function getArguments(): array
        {
        }

        public function newInstance(): object
        {
        }
    }
}

if (!class_exists('ReflectionEnum')) {
    class ReflectionEnum extends ReflectionClass
    {
        public function __construct($objectOrClass)
        {
        }

        public function getAttributes(?string $name = null, int $flags = 0): array
        {
        }

        public function getBackingType(): ?ReflectionNamedType
        {
        }

        public function getCase(string $name): ReflectionEnumUnitCase
        {
        }

        public function getCases(): array
        {
        }

        public function hasCase(string $name): bool
        {
        }

        public function isBacked(): bool
        {
        }
    }
}

if (!class_exists('ReflectionEnumUnitCase')) {
    class ReflectionEnumUnitCase extends ReflectionClassConstant
    {
        public function __construct($class, string $constant)
        {
        }

        public function getEnum(): ReflectionEnum
        {
        }

        #[\ReturnTypeWillChange]
        public function getValue(): int|string
        {
        }

        public function getAttributes(?string $name = null, int $flags = 0): array
        {
        }
    }
}

if (!class_exists('ReflectionEnumBackedCase')) {
    class ReflectionEnumBackedCase extends ReflectionEnumUnitCase
    {
        public function __construct($class, string $constant)
        {
        }

        #[ReturnTypeWillChange]
        public function getBackingValue(): int|string
        {
        }
    }
}

if (!class_exists('AllowDynamicProperties')) {
    #[Attribute] final class AllowDynamicProperties
    {
        public function __construct()
        {
        }
    }
}
