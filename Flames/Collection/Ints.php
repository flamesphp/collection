<?php

declare(strict_types=1);

namespace Flames\Collection;

/**
 * Stateless utility class for common integer operations.
 */
final class Ints
{
    public static function parse(mixed $value): int
    {
        return (int) $value;
    }

    /**
     * Clamps $value to the inclusive range [$min, $max].
     */
    public static function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }

    /**
     * Returns true when $value is within the inclusive range [$min, $max].
     */
    public static function between(int $value, int $min, int $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    /**
     * Returns true when $value is even.
     */
    public static function isEven(int $value): bool
    {
        return ($value % 2) === 0;
    }

    /**
     * Returns true when $value is odd.
     */
    public static function isOdd(int $value): bool
    {
        return ($value % 2) !== 0;
    }

    /**
     * Generates a cryptographically secure random integer in the inclusive range [$min, $max].
     */
    public static function getRandom(int $min = 0, int $max = PHP_INT_MAX): int
    {
        return random_int($min, $max);
    }
}
