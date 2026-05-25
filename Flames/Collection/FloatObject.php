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
        return match (strtolower($name)) {
            'value' => $this->value,
            default => null,
        };
    }

    /**
     * Clamps the value to the inclusive range [$min, $max] (mutating).
     */
    public function clamp(float $min, float $max): self
    {
        $this->value = Floats::clamp($this->value, $min, $max);
        return $this;
    }

    /**
     * Returns true when the value is within the inclusive range [$min, $max].
     */
    public function between(float $min, float $max): bool
    {
        return Floats::between($this->value, $min, $max);
    }

    /**
     * Rounds to $precision decimal places (mutating).
     *
     * Accepts PHP_ROUND_HALF_* constants for $mode.
     */
    public function round(int $precision = 0, int $mode = PHP_ROUND_HALF_UP): self
    {
        $this->value = Floats::round($this->value, $precision, $mode);
        return $this;
    }

    /**
     * Rounds down to the nearest integer (mutating).
     */
    public function floor(): self
    {
        $this->value = Floats::floor($this->value);
        return $this;
    }

    /**
     * Rounds up to the nearest integer (mutating).
     */
    public function ceil(): self
    {
        $this->value = Floats::ceil($this->value);
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
            throw new \DivisionByZeroError('Division by zero.');
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
        return Floats::format($this->value, $decimals, $decimalSep, $thousandsSep);
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
        return Bools::parse((int) $this->value);
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
