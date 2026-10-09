<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Collection\Trait\Prototype as PrototypeTrait;

/**
 * Mutable 2D vector (x, y), modelled after C# System.Numerics.Vector2.
 */
final class Vector2 implements \Stringable
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

    /**
     * @param array{0?: float|int, 1?: float|int, x?: float|int, y?: float|int}|Arr|object|self|Vector2Immutable $value
     */
    public static function parse(mixed $value, mixed $y = null): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value instanceof Vector2Immutable) {
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

        throw new \InvalidArgumentException('Cannot parse Vector2 from the given value.');
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
            $this->x = 0.0;
            $this->y = 0.0;
            return $this;
        }
        $this->x /= $len;
        $this->y /= $len;
        return $this;
    }

    public function add(self $other): self
    {
        $this->x += $other->x;
        $this->y += $other->y;
        return $this;
    }

    public function subtract(self $other): self
    {
        $this->x -= $other->x;
        $this->y -= $other->y;
        return $this;
    }

    /**
     * Multiplies by a scalar or component-wise by another vector.
     */
    public function multiply(float|self $value): self
    {
        if ($value instanceof self) {
            $this->x *= $value->x;
            $this->y *= $value->y;
            return $this;
        }
        $this->x *= $value;
        $this->y *= $value;
        return $this;
    }

    /**
     * Divides by a scalar or component-wise by another vector.
     *
     * @throws \DivisionByZeroError
     */
    public function divide(float|self $value): self
    {
        if ($value instanceof self) {
            $this->x /= $value->x;
            $this->y /= $value->y;
            return $this;
        }
        if ($value === 0.0) {
            throw new \DivisionByZeroError('Division by zero.');
        }
        $this->x /= $value;
        $this->y /= $value;
        return $this;
    }

    public function negate(): self
    {
        $this->x = -$this->x;
        $this->y = -$this->y;
        return $this;
    }

    public function reflect(self $normal): self
    {
        $dot = self::dot($this, $normal) * 2.0;
        $this->x -= $normal->x * $dot;
        $this->y -= $normal->y * $dot;
        return $this;
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
        $this->x += ($other->x - $this->x) * $t;
        $this->y += ($other->y - $this->y) * $t;
        return $this;
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

    public function toImmutable(): Vector2Immutable
    {
        return new Vector2Immutable($this->x, $this->y);
    }

    public function toVector3(float $z = 0.0): Vector3
    {
        return new Vector3($this->x, $this->y, $z);
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
