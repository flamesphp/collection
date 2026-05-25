<?php

declare(strict_types=1);

namespace Flames\Collection;

/**
 * Fluent, mutable wrapper around a single PHP integer.
 *
 * Mutating methods return $this so calls can be chained:
 *
 * ```php
 * $result = (new IntObject(150))
 *     ->clamp(0, 100)
 *     ->toInt();   // 100
 * ```
 *
 * @property-read int $value  The current integer value.
 */
final class IntObject
{
    private int $value;

    public function __construct(mixed $value = 0)
    {
        $this->value = Ints::parse($value);
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
    public function clamp(int $min, int $max): self
    {
        $this->value = max($min, min($max, $this->value));
        return $this;
    }

    /**
     * Returns true when the value is within the inclusive range [$min, $max].
     */
    public function between(int $min, int $max): bool
    {
        return $this->value >= $min && $this->value <= $max;
    }

    /**
     * Returns true when the value is even.
     */
    public function isEven(): bool
    {
        return ($this->value & 1) === 0;
    }

    /**
     * Returns true when the value is odd.
     */
    public function isOdd(): bool
    {
        return ($this->value & 1) !== 0;
    }

    /**
     * Returns true when the value equals zero.
     */
    public function isZero(): bool
    {
        return $this->value === 0;
    }

    /**
     * Returns true when the value is greater than zero.
     */
    public function isPositive(): bool
    {
        return $this->value > 0;
    }

    /**
     * Returns true when the value is less than zero.
     */
    public function isNegative(): bool
    {
        return $this->value < 0;
    }

    /**
     * Adds $amount to the value (mutating).
     */
    public function add(int $amount): self
    {
        $this->value += $amount;
        return $this;
    }

    /**
     * Subtracts $amount from the value (mutating).
     */
    public function subtract(int $amount): self
    {
        $this->value -= $amount;
        return $this;
    }

    /**
     * Multiplies the value by $factor (mutating).
     */
    public function multiply(int $factor): self
    {
        $this->value *= $factor;
        return $this;
    }

    /**
     * Divides the value by $divisor using integer division (mutating).
     *
     * Throws \DivisionByZeroError when $divisor is 0.
     */
    public function divide(int $divisor): self
    {
        $this->value = intdiv($this->value, $divisor);
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

    public function toInt(): int
    {
        return $this->value;
    }

    public function toFloat(): float
    {
        return (float) $this->value;
    }

    public function toString(): string
    {
        return (string) $this->value;
    }

    public function toBool(): bool|null
    {
        return match ($this->value) {
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
