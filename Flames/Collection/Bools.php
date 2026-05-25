<?php

declare(strict_types=1);

namespace Flames\Collection;

/**
 * Stateless utility class for common boolean operations.
 */
final class Bools
{
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
            return match (strtolower($value)) {
                'true',  'yes', '1' => true,
                'false', 'no',  '0' => false,
                default             => null,
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
}
