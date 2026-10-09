<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Collection\Trait\Prototype as PrototypeTrait;

/**
 * Immutable 2D vector (x, y), modelled after C# System.Numerics.Vector2.
 */
final readonly class Vector2Immutable implements \Stringable
{
    use PrototypeTrait;

    public function __construct(
        public float $x = 0.0,
        public float $y = 0.0,
    ) {
    }

    public static function zero(): self
    {
        return new self();
    }

    public static function one(): self
    {
        return new self(1.0, 1.0);
    }

    public static function unitX(): self
    {
        return new self(1.0, 0.0);
    }

    public static function unitY(): self
    {
        return new self(0.0, 1.0);
    }

    public function withX(float $x): self
    {
        return new self($x, $this->y);
    }

    public function withY(float $y): self
    {
        return new self($this->x, $y);
    }

    /**
     * @param array{0?: float|int, 1?: float|int, x?: float|int, y?: float|int}|Arr|object|self|Vector2 $value
     */
    public static function parse(mixed $value, mixed $y = null): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value instanceof Vector2) {
            return new self($value->x, $value->y);
        }

        if ($y !== null) {
            return new self((float) $value, (float) $y);
        }

        if ($value instanceof Arr) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            return new self(
                (float) ($value['x'] ?? $value[0] ?? 0.0),
                (float) ($value['y'] ?? $value[1] ?? 0.0),
            );
        }

        if (is_object($value)) {
            return new self((float) ($value->x ?? 0.0), (float) ($value->y ?? 0.0));
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if (preg_match('/^\(([-\d.]+)\s*,\s*([-\d.]+)\)$/', $trimmed, $matches) === 1) {
                return new self((float) $matches[1], (float) $matches[2]);
            }
        }

        throw new \InvalidArgumentException('Cannot parse Vector2Immutable from the given value.');
    }

    public static function distance(self $a, self $b): float
    {
        return sqrt(self::distanceSquared($a, $b));
    }

    public static function distanceSquared(self $a, self $b): float
    {
        $dx = $a->x - $b->x;
        $dy = $a->y - $b->y;
        return ($dx * $dx) + ($dy * $dy);
    }

    public static function dot(self $a, self $b): float
    {
        return ($a->x * $b->x) + ($a->y * $b->y);
    }

    public static function lerp(self $a, self $b, float $t): self
    {
        return new self(
            $a->x + (($b->x - $a->x) * $t),
            $a->y + (($b->y - $a->y) * $t),
        );
    }

    public static function min(self $a, self $b): self
    {
        return new self(min($a->x, $b->x), min($a->y, $b->y));
    }

    public static function max(self $a, self $b): self
    {
        return new self(max($a->x, $b->x), max($a->y, $b->y));
    }

    public static function clamp(self $value, self $min, self $max): self
    {
        return self::max($min, self::min($max, $value));
    }

    public function lengthSquared(): float
    {
        return ($this->x * $this->x) + ($this->y * $this->y);
    }

    public function length(): float
    {
        return sqrt($this->lengthSquared());
    }

    public function normalize(): self
    {
        $len = $this->length();
        if ($len <= 0.0) {
            return self::zero();
        }
        return $this->divide($len);
    }

    public function add(self $other): self
    {
        return new self($this->x + $other->x, $this->y + $other->y);
    }

    public function subtract(self $other): self
    {
        return new self($this->x - $other->x, $this->y - $other->y);
    }

    /**
     * Multiplies by a scalar or component-wise by another vector.
     */
    public function multiply(float|self $value): self
    {
        if ($value instanceof self) {
            return new self($this->x * $value->x, $this->y * $value->y);
        }
        return new self($this->x * $value, $this->y * $value);
    }

    /**
     * Divides by a scalar or component-wise by another vector.
     *
     * @throws \DivisionByZeroError
     */
    public function divide(float|self $value): self
    {
        if ($value instanceof self) {
            return new self($this->x / $value->x, $this->y / $value->y);
        }
        if ($value === 0.0) {
            throw new \DivisionByZeroError('Division by zero.');
        }
        return new self($this->x / $value, $this->y / $value);
    }

    public function negate(): self
    {
        return new self(-$this->x, -$this->y);
    }

    public function reflect(self $normal): self
    {
        $dot = self::dot($this, $normal) * 2.0;
        return new self($this->x - ($normal->x * $dot), $this->y - ($normal->y * $dot));
    }

    public function distanceTo(self $other): float
    {
        return self::distance($this, $other);
    }

    public function distanceSquaredTo(self $other): float
    {
        return self::distanceSquared($this, $other);
    }

    public function dotWith(self $other): float
    {
        return self::dot($this, $other);
    }

    public function lerpTo(self $other, float $t): self
    {
        return self::lerp($this, $other, $t);
    }

    public function equals(self $other, float $epsilon = 1e-6): bool
    {
        return abs($this->x - $other->x) <= $epsilon
            && abs($this->y - $other->y) <= $epsilon;
    }

    public function isZero(float $epsilon = 1e-6): bool
    {
        return $this->lengthSquared() <= ($epsilon * $epsilon);
    }

    public function toMutable(): Vector2
    {
        return new Vector2($this->x, $this->y);
    }

    public function toVector3(float $z = 0.0): Vector3Immutable
    {
        return new Vector3Immutable($this->x, $this->y, $z);
    }

    /**
     * @return array{x: float, y: float}
     */
    public function toArray(): array
    {
        return ['x' => $this->x, 'y' => $this->y];
    }

    public function toArr(): Arr
    {
        return new Arr($this->toArray());
    }

    public function __toString(): string
    {
        return sprintf('(%s, %s)', $this->x, $this->y);
    }
}
