<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Collection\Trait\Prototype as PrototypeTrait;

/**
 * Stateless utility class for PHP enum operations.
 */
final class Enums
{
    use PrototypeTrait;

    public static function isEnum(mixed $value): bool
    {
        return is_object($value) && $value instanceof \UnitEnum;
    }

    public static function isBackedEnum(mixed $value): bool
    {
        return is_object($value) && $value instanceof \BackedEnum;
    }

    public static function isEnumClass(string $class): bool
    {
        return enum_exists($class);
    }

    public static function isBackedEnumClass(string $class): bool
    {
        return enum_exists($class) && is_subclass_of($class, \BackedEnum::class);
    }

    /**
     * @template T of \UnitEnum
     * @param  class-string<T> $class
     * @return T|null
     */
    public static function tryFrom(string $class, mixed $value): \UnitEnum|null
    {
        self::assertEnumClass($class);

        if (!is_subclass_of($class, \BackedEnum::class)) {
            throw new \InvalidArgumentException(sprintf('Enum %s is not backed.', $class));
        }

        /** @var \BackedEnum|null $result */
        $result = $class::tryFrom($value);
        return $result;
    }

    /**
     * @template T of \UnitEnum
     * @param  class-string<T> $class
     * @return T
     */
    public static function from(string $class, mixed $value): \UnitEnum
    {
        $enum = self::tryFrom($class, $value);
        if ($enum === null) {
            throw new \ValueError(sprintf('Invalid value for enum %s.', $class));
        }
        return $enum;
    }

    /**
     * @template T of \UnitEnum
     * @param  class-string<T> $class
     * @return list<T>
     */
    public static function cases(string $class): array
    {
        self::assertEnumClass($class);
        return $class::cases();
    }

    /**
     * @return list<string>
     */
    public static function names(string $class): array
    {
        return array_map(
            static fn (\UnitEnum $case): string => $case->name,
            self::cases($class),
        );
    }

    /**
     * @return list<int|string>
     */
    public static function values(string $class): array
    {
        self::assertBackedEnumClass($class);
        return array_map(
            static fn (\BackedEnum $case): int|string => $case->value,
            self::cases($class),
        );
    }

    public static function name(\UnitEnum $value): string
    {
        return $value->name;
    }

    public static function value(\BackedEnum $value): int|string
    {
        return $value->value;
    }

    public static function equals(\UnitEnum $value, \UnitEnum $other): bool
    {
        return $value === $other;
    }

    public static function tryParse(string $class, mixed $value): \UnitEnum|null
    {
        if (self::isEnum($value) && is_a($value, $class)) {
            return $value;
        }

        if (is_string($value) && enum_exists($class)) {
            foreach ($class::cases() as $case) {
                if ($case->name === $value) {
                    return $case;
                }
            }
        }

        if (is_subclass_of($class, \BackedEnum::class)) {
            return self::tryFrom($class, $value);
        }

        return null;
    }

    /**
     * @template T of \UnitEnum
     * @param  class-string<T> $class
     * @return T
     */
    public static function parse(string $class, mixed $value): \UnitEnum
    {
        $enum = self::tryParse($class, $value);
        if ($enum === null) {
            throw new \InvalidArgumentException(sprintf('Cannot parse enum %s from the given value.', $class));
        }
        return $enum;
    }

    private static function assertEnumClass(string $class): void
    {
        if (!enum_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class %s is not an enum.', $class));
        }
    }

    private static function assertBackedEnumClass(string $class): void
    {
        self::assertEnumClass($class);
        if (!is_subclass_of($class, \BackedEnum::class)) {
            throw new \InvalidArgumentException(sprintf('Enum %s is not backed.', $class));
        }
    }
}
