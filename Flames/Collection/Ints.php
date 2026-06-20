<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Collection\Trait\Prototype as PrototypeTrait;
use Random\RandomException;

/**
 * Stateless utility class for common integer operations.
 */
final class Ints
{
    use PrototypeTrait;

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
        return ($value & 1) === 0;
    }

    /**
     * Returns true when $value is odd.
     */
    public static function isOdd(int $value): bool
    {
        return ($value & 1) !== 0;
    }

    /**
     * Returns true when $value equals zero.
     */
    public static function isZero(int $value): bool
    {
        return $value === 0;
    }

    /**
     * Returns true when $value is greater than zero.
     */
    public static function isPositive(int $value): bool
    {
        return $value > 0;
    }

    /**
     * Returns true when $value is less than zero.
     */
    public static function isNegative(int $value): bool
    {
        return $value < 0;
    }

    /**
     * Adds $amount to $value and returns the result.
     */
    public static function add(int $value, int $amount): int
    {
        return $value + $amount;
    }

    /**
     * Subtracts $amount from $value and returns the result.
     */
    public static function subtract(int $value, int $amount): int
    {
        return $value - $amount;
    }

    /**
     * Multiplies $value by $factor and returns the result.
     */
    public static function multiply(int $value, int $factor): int
    {
        return $value * $factor;
    }

    /**
     * Divides $value by $divisor using integer division and returns the result.
     *
     * @throws \DivisionByZeroError when $divisor is 0.
     */
    public static function divide(int $value, int $divisor): int
    {
        return intdiv($value, $divisor);
    }

    /**
     * Returns the absolute value of $value.
     */
    public static function abs(int $value): int
    {
        return abs($value);
    }

    /**
     * Generates a cryptographically secure random integer in the inclusive range [$min, $max].
     * @throws RandomException
     */
    public static function getRandom(int $min = 0, int $max = PHP_INT_MAX): int
    {
        return random_int($min, $max);
    }

    /**
     * Returns the remainder of integer division (modulo).
     */
    public static function mod(int $value, int $divisor): int
    {
        return $value % $divisor;
    }

    /**
     * Raises $value to the $exponent power (int).
     */
    public static function power(int $value, int $exponent): int
    {
        return $value ** $exponent;
    }

    /**
     * Returns the smaller of $value and $others (min).
     */
    public static function min(int $value, int ...$others): int
    {
        return min($value, ...$others);
    }

    /**
     * Returns the larger of $value and $others (max).
     */
    public static function max(int $value, int ...$others): int
    {
        return max($value, ...$others);
    }

    /**
     * Returns -1, 0, or 1 according to the sign of $value.
     */
    public static function sign(int $value): int
    {
        return $value <=> 0;
    }

    public static function increment(int $value): int
    {
        return $value + 1;
    }

    public static function decrement(int $value): int
    {
        return $value - 1;
    }

    public static function toBinary(int $value): string
    {
        return decbin($value);
    }

    public static function toHex(int $value): string
    {
        return dechex($value);
    }

    public static function toOctal(int $value): string
    {
        return decoct($value);
    }

    public static function fromBinary(mixed $value): int
    {
        return bindec((string) $value);
    }

    public static function fromHex(mixed $value): int
    {
        return hexdec((string) $value);
    }

    public static function fromOctal(mixed $value): int
    {
        return octdec((string) $value);
    }

    public static function gcd(int $value, int $other): int
    {
        return self::calculateGcd(abs($value), abs($other));
    }

    public static function lcm(int $value, int $other): int
    {
        if ($value === 0 || $other === 0) {
            return 0;
        }
        return intdiv(abs($value * $other), self::calculateGcd(abs($value), abs($other)));
    }

    private static function calculateGcd(int $a, int $b): int
    {
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }
        return $a;
    }
}
