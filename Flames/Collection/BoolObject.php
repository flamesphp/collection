<?php

declare(strict_types=1);

namespace Flames\Collection;

/**
 * Fluent, mutable wrapper around a PHP boolean (or null for ambiguous values).
 *
 * The wrapped value follows the same parsing rules as Bools::parse():
 *   true, false, or null (for ambiguous / unknown values).
 *
 * ```php
 * $obj = new BoolObject('yes');
 * $obj->isTrue();   // true
 * $obj->negate()->isTrue();  // false
 * ```
 *
 * @property-read bool|null $value  The current boolean value.
 */
final class BoolObject
{
    private bool|null $value;

    public function __construct(mixed $value = null)
    {
        $this->value = Bools::parse($value);
    }

    public function __get(string $name): mixed
    {
        return match (strtolower($name)) {
            'value' => $this->value,
            default => null,
        };
    }

    /**
     * Returns true when the wrapped value is true.
     */
    public function isTrue(): bool
    {
        return $this->value === true;
    }

    /**
     * Returns true when the wrapped value is false.
     */
    public function isFalse(): bool
    {
        return $this->value === false;
    }

    /**
     * Returns true when the wrapped value is null (ambiguous / unresolved).
     */
    public function isNull(): bool
    {
        return $this->value === null;
    }

    /**
     * Negates the wrapped value (mutating).
     *
     * true becomes false, false becomes true, null stays null.
     */
    public function negate(): self
    {
        if ($this->value !== null) {
            $this->value = !$this->value;
        }
        return $this;
    }

    /**
     * Applies logical AND with $other (mutating).
     */
    public function and(bool $other): self
    {
        $this->value = ($this->value === true) && $other;
        return $this;
    }

    /**
     * Applies logical OR with $other (mutating).
     */
    public function or(bool $other): self
    {
        $this->value = ($this->value === true) || $other;
        return $this;
    }

    public function toBool(): bool|null
    {
        return $this->value;
    }

    public function toInt(): int
    {
        return $this->value === true ? 1 : ($this->value === false ? 0 : -1);
    }

    public function toString(): string
    {
        return match ($this->value) {
            true    => 'true',
            false   => 'false',
            default => 'null',
        };
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
