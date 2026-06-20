<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Collection\Trait\Prototype as PrototypeTrait;

/**
 * Stateless utility class for common floating-point operations.
 */
final class Floats
{
    use PrototypeTrait;

    public static function parse(mixed $value): float
    {
        return (float) $value;
    }

    /**
     * Clamps $value to the inclusive range [$min, $max].
     */
    public static function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }

    /**
     * Returns true when $value is within the inclusive range [$min, $max].
     */
    public static function between(float $value, float $min, float $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    /**
     * Returns true when $value equals zero.
     */
    public static function isZero(float $value): bool
    {
        return $value === 0.0;
    }

    /**
     * Returns true when $value is greater than zero.
     */
    public static function isPositive(float $value): bool
    {
        return $value > 0.0;
    }

    /**
     * Returns true when $value is less than zero.
     */
    public static function isNegative(float $value): bool
    {
        return $value < 0.0;
    }

    /**
     * Adds $amount to $value and returns the result.
     */
    public static function add(float $value, float $amount): float
    {
        return $value + $amount;
    }

    /**
     * Subtracts $amount from $value and returns the result.
     */
    public static function subtract(float $value, float $amount): float
    {
        return $value - $amount;
    }

    /**
     * Multiplies $value by $factor and returns the result.
     */
    public static function multiply(float $value, float $factor): float
    {
        return $value * $factor;
    }

    /**
     * Divides $value by $divisor and returns the result.
     *
     * @throws \DivisionByZeroError when $divisor is 0.
     */
    public static function divide(float $value, float $divisor): float
    {
        if ($divisor === 0.0) {
            throw new \DivisionByZeroError('Division by zero.');
        }
        return $value / $divisor;
    }

    /**
     * Returns the absolute value of $value.
     */
    public static function abs(float $value): float
    {
        return abs($value);
    }

    /**
     * Rounds $value to $precision decimal places.
     *
     * Accepts a \RoundingMode enum (PHP 8.4+) or the legacy PHP_ROUND_HALF_* int constants.
     */
    public static function round(float $value, int $precision = 0, \RoundingMode|int $mode = \RoundingMode::HalfAwayFromZero): float
    {
        return round($value, $precision, $mode);
    }

    /**
     * Rounds $value down to the nearest integer (towards negative infinity).
     */
    public static function floor(float $value): float
    {
        return floor($value);
    }

    /**
     * Rounds $value up to the nearest integer (towards positive infinity).
     */
    public static function ceil(float $value): float
    {
        return ceil($value);
    }

    /**
     * Formats $value as a localised decimal string.
     *
     * @param string $decimalSep   Decimal separator (default: '.').
     * @param string $thousandsSep Thousands separator (default: '').
     */
    public static function format(
        float  $value,
        int    $decimals     = 2,
        string $decimalSep   = '.',
        string $thousandsSep = '',
    ): string {
        return number_format($value, $decimals, $decimalSep, $thousandsSep);
    }

    /**
     * Returns the floating-point remainder of division (fmod).
     */
    public static function mod(float $value, float $divisor): float
    {
        return fmod($value, $divisor);
    }

    /**
     * Raises $value to the $exponent power.
     */
    public static function power(float $value, float $exponent): float
    {
        return $value ** $exponent;
    }

    public static function sqrt(float $value): float
    {
        return sqrt($value);
    }

    public static function log(float $value, float $base = M_E): float
    {
        return $base === M_E ? log($value) : log($value, $base);
    }

    public static function exp(float $value): float
    {
        return exp($value);
    }

    public static function min(float $value, float ...$others): float
    {
        return min($value, ...$others);
    }

    public static function max(float $value, float ...$others): float
    {
        return max($value, ...$others);
    }

    public static function sign(float $value): int
    {
        return $value <=> 0.0;
    }

    public static function isFinite(float $value): bool
    {
        return is_finite($value);
    }

    public static function isNan(float $value): bool
    {
        return is_nan($value);
    }

    public static function isInfinite(float $value): bool
    {
        return is_infinite($value);
    }

    public static function toInt(float $value): int
    {
        return (int) $value;
    }
}
