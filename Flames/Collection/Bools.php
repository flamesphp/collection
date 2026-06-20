<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Collection\Trait\Prototype as PrototypeTrait;

/**
 * Stateless utility class for common boolean operations.
 */
final class Bools
{
    use PrototypeTrait;

    /**
     * Converts $value to a boolean, or null for ambiguous / unknown inputs.
     *
     * Recognised truthy  : true, 1, 'true', 'yes', '1'
     * Recognised falsy   : false, 0, 'false', 'no', '0'
     * Recognised nullable: '-1'
     * String comparison is case-insensitive.
     *
     * All other types return null.
     */
    public static function parse(mixed $value): bool|null
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return match ($value) {
                1       => true,
                0       => false,
                default => null,
            };
        }

        if (is_string($value)) {
            return match ($value) {
                'true', 'yes', '1' => true,
                'false', 'no', '0' => false,
                default            => match (strtolower($value)) {
                    'true', 'yes', '1' => true,
                    'false', 'no', '0' => false,
                    default            => null,
                },
            };
        }

        return null;
    }

    /**
     * Returns true when $value parses to true.
     */
    public static function isTrue(mixed $value): bool
    {
        return self::parse($value) === true;
    }

    /**
     * Returns true when $value parses to false.
     */
    public static function isFalse(mixed $value): bool
    {
        return self::parse($value) === false;
    }

    /**
     * Returns true when $value parses to null (ambiguous / unresolved).
     */
    public static function isNull(mixed $value): bool
    {
        return self::parse($value) === null;
    }

    /**
     * Negates $value: true → false, false → true, null → null.
     */
    public static function negate(bool|null $value): bool|null
    {
        return $value !== null ? !$value : null;
    }

    public static function and(bool $value, bool $other): bool
    {
        return $value && $other;
    }

    public static function or(bool $value, bool $other): bool
    {
        return $value || $other;
    }

    public static function xor(bool $value, bool $other): bool
    {
        return $value xor $other;
    }

    public static function toInt(bool $value): int
    {
        return (int) $value;
    }

    /**
     * Converts 0/1 to false/true. Any other value returns null.
     */
    public static function fromInt(int $value): bool|null
    {
        return match ($value) {
            1       => true,
            0       => false,
            default => null,
        };
    }

    public static function toString(bool $value): string
    {
        return $value ? 'true' : 'false';
    }
}
