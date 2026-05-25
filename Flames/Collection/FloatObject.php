<?php

declare(strict_types=1);

namespace Flames\Collection;

/**
 * Fluent, mutable wrapper around a single PHP float.
 *
 * Mutating methods return $this so calls can be chained:
 *
 * ```php
 * $result = (new FloatObject(3.14159))
 *     ->round(2)
 *     ->toFloat();   // 3.14
 * ```
 *
 * @property-read float $value  The current float value.
 */
final class FloatObject
{
    private float $value;

    public function __construct(mixed $value = 0.0)
    {
        $this->value = Floats::parse($value);
    }

    public function __get(string $name): mixed
    {
        if ($name === 'value') {
            return $this->value;
        }
        return strtolower($name) === 'value' ? $this->value : null;
    }

    /**
     * Clamps the value to the inclusive range [$min, $max] (mutating).
     */
    public function clamp(float $min, float $max): self
    {
        $this->value = max($min, min($max, $this->value));
        return $this;
    }

    /**
     * Returns true when the value is within the inclusive range [$min, $max].
     */
    public function between(float $min, float $max): bool
    {
        return $this->value >= $min && $this->value <= $max;
    }

    /**
     * Rounds to $precision decimal places (mutating).
     *
     * Accepts a \RoundingMode enum (PHP 8.4+) or the legacy PHP_ROUND_HALF_* int constants.
     */
    public function round(int $precision = 0, \RoundingMode|int $mode = \RoundingMode::HalfAwayFromZero): self
    {
        $this->value = round($this->value, $precision, $mode);
        return $this;
    }

    /**
     * Rounds down to the nearest integer (mutating).
     */
    public function floor(): self
    {
        $this->value = floor($this->value);
        return $this;
    }

    /**
     * Rounds up to the nearest integer (mutating).
     */
    public function ceil(): self
    {
        $this->value = ceil($this->value);
        return $this;
    }

    /**
     * Returns true when the value equals zero.
     */
    public function isZero(): bool
    {
        return $this->value === 0.0;
    }

    /**
     * Returns true when the value is greater than zero.
     */
    public function isPositive(): bool
    {
        return $this->value > 0.0;
    }

    /**
     * Returns true when the value is less than zero.
     */
    public function isNegative(): bool
    {
        return $this->value < 0.0;
    }

    /**
     * Adds $amount to the value (mutating).
     */
    public function add(float $amount): self
    {
        $this->value += $amount;
        return $this;
    }

    /**
     * Subtracts $amount from the value (mutating).
     */
    public function subtract(float $amount): self
    {
        $this->value -= $amount;
        return $this;
    }

    /**
     * Multiplies the value by $factor (mutating).
     */
    public function multiply(float $factor): self
    {
        $this->value *= $factor;
        return $this;
    }

    /**
     * Divides the value by $divisor (mutating).
     *
     * Throws \DivisionByZeroError when $divisor is 0.
     */
    public function divide(float $divisor): self
    {
        if ($divisor === 0.0) {
            $this->value = 0;
            return $this;
        }
        $this->value /= $divisor;
        return $this;
    }

    /**
     * Sets the value to its absolute form (mutating).
     */
    public function abs(): self
    {
        $this->value = abs($this->value);
        return $this;
    }

    /**
     * Formats the value as a localised decimal string.
     */
    public function format(int $decimals = 2, string $decimalSep = '.', string $thousandsSep = ''): string
    {
        return number_format($this->value, $decimals, $decimalSep, $thousandsSep);
    }

    public function toFloat(): float
    {
        return $this->value;
    }

    public function toInt(): int
    {
        return (int) $this->value;
    }

    public function toString(): string
    {
        return (string) $this->value;
    }

    public function toBool(): bool|null
    {
        return match ((int) $this->value) {
            1       => true,
            0       => false,
            default => null,
        };
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
