<?php

declare(strict_types=1);

namespace Flames\Collection;

/**
 * Stateless utility class for common floating-point operations.
 */
final class Floats
{
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
     * Rounds $value to $precision decimal places.
     *
     * Accepts PHP_ROUND_HALF_UP, PHP_ROUND_HALF_DOWN, PHP_ROUND_HALF_EVEN,
     * or PHP_ROUND_HALF_ODD for $mode.
     */
    public static function round(float $value, int $precision = 0, int $mode = PHP_ROUND_HALF_UP): float
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
}
