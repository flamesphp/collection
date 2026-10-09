<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Collection\Trait\Prototype as PrototypeTrait;

/**
 * Immutable 3D vector (x, y, z), modelled after C# System.Numerics.Vector3.
 */
final readonly class Vector3Immutable implements \Stringable
{
    use PrototypeTrait;

    public function __construct(
        public float $x = 0.0,
        public float $y = 0.0,
        public float $z = 0.0,
    ) {
    }

    public static function zero(): self
    {
        return new self();
    }

    public static function one(): self
    {
        return new self(1.0, 1.0, 1.0);
    }

    public static function unitX(): self
    {
        return new self(1.0, 0.0, 0.0);
    }

    public static function unitY(): self
    {
        return new self(0.0, 1.0, 0.0);
    }

    public static function unitZ(): self
    {
        return new self(0.0, 0.0, 1.0);
    }

    public function withX(float $x): self
    {
        return new self($x, $this->y, $this->z);
    }

    public function withY(float $y): self
    {
        return new self($this->x, $y, $this->z);
    }

    public function withZ(float $z): self
    {
        return new self($this->x, $this->y, $z);
    }

    /**
     * @param array{0?: float|int, 1?: float|int, 2?: float|int, x?: float|int, y?: float|int, z?: float|int}|Arr|object|self|Vector2|Vector2Immutable|Vector3 $value
     */
    public static function parse(mixed $value, mixed $y = null, mixed $z = null): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value instanceof Vector3) {
            return new self($value->x, $value->y, $value->z);
        }

        if ($value instanceof Vector2 || $value instanceof Vector2Immutable) {
            return new self($value->x, $value->y, (float) ($z ?? 0.0));
        }

        if ($y !== null && $z !== null) {
            return new self((float) $value, (float) $y, (float) $z);
        }

        if ($value instanceof Arr) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            return new self(
                (float) ($value['x'] ?? $value[0] ?? 0.0),
                (float) ($value['y'] ?? $value[1] ?? 0.0),
                (float) ($value['z'] ?? $value[2] ?? 0.0),
            );
        }

        if (is_object($value)) {
            return new self(
                (float) ($value->x ?? 0.0),
                (float) ($value->y ?? 0.0),
                (float) ($value->z ?? 0.0),
            );
        }

        throw new \InvalidArgumentException('Cannot parse Vector3Immutable from the given value.');
    }

    public static function distance(self $a, self $b): float
    {
        return sqrt(self::distanceSquared($a, $b));
    }

    public static function distanceSquared(self $a, self $b): float
    {
        $dx = $a->x - $b->x;
        $dy = $a->y - $b->y;
        $dz = $a->z - $b->z;
        return ($dx * $dx) + ($dy * $dy) + ($dz * $dz);
    }

    public static function dot(self $a, self $b): float
    {
        return ($a->x * $b->x) + ($a->y * $b->y) + ($a->z * $b->z);
    }

    public static function cross(self $a, self $b): self
    {
        return new self(
            ($a->y * $b->z) - ($a->z * $b->y),
            ($a->z * $b->x) - ($a->x * $b->z),
            ($a->x * $b->y) - ($a->y * $b->x),
        );
    }

    public static function lerp(self $a, self $b, float $t): self
    {
        return new self(
            $a->x + (($b->x - $a->x) * $t),
            $a->y + (($b->y - $a->y) * $t),
            $a->z + (($b->z - $a->z) * $t),
        );
    }

    public static function min(self $a, self $b): self
    {
        return new self(min($a->x, $b->x), min($a->y, $b->y), min($a->z, $b->z));
    }

    public static function max(self $a, self $b): self
    {
        return new self(max($a->x, $b->x), max($a->y, $b->y), max($a->z, $b->z));
    }

    public static function clamp(self $value, self $min, self $max): self
    {
        return self::max($min, self::min($max, $value));
    }

    public function lengthSquared(): float
    {
        return ($this->x * $this->x) + ($this->y * $this->y) + ($this->z * $this->z);
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
        return new self($this->x + $other->x, $this->y + $other->y, $this->z + $other->z);
    }

    public function subtract(self $other): self
    {
        return new self($this->x - $other->x, $this->y - $other->y, $this->z - $other->z);
    }

    /**
     * Multiplies by a scalar or component-wise by another vector.
     */
    public function multiply(float|self $value): self
    {
        if ($value instanceof self) {
            return new self($this->x * $value->x, $this->y * $value->y, $this->z * $value->z);
        }
        return new self($this->x * $value, $this->y * $value, $this->z * $value);
    }

    /**
     * Divides by a scalar or component-wise by another vector.
     *
     * @throws \DivisionByZeroError
     */
    public function divide(float|self $value): self
    {
        if ($value instanceof self) {
            return new self($this->x / $value->x, $this->y / $value->y, $this->z / $value->z);
        }
        if ($value === 0.0) {
            throw new \DivisionByZeroError('Division by zero.');
        }
        return new self($this->x / $value, $this->y / $value, $this->z / $value);
    }

    public function negate(): self
    {
        return new self(-$this->x, -$this->y, -$this->z);
    }

    public function crossWith(self $other): self
    {
        return self::cross($this, $other);
    }

    public function reflect(self $normal): self
    {
        $dot = self::dot($this, $normal) * 2.0;
        return new self(
            $this->x - ($normal->x * $dot),
            $this->y - ($normal->y * $dot),
            $this->z - ($normal->z * $dot),
        );
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
            && abs($this->y - $other->y) <= $epsilon
            && abs($this->z - $other->z) <= $epsilon;
    }

    public function isZero(float $epsilon = 1e-6): bool
    {
        return $this->lengthSquared() <= ($epsilon * $epsilon);
    }

    public function toMutable(): Vector3
    {
        return new Vector3($this->x, $this->y, $this->z);
    }

    public function toVector2(): Vector2Immutable
    {
        return new Vector2Immutable($this->x, $this->y);
    }

    /**
     * @return array{x: float, y: float, z: float}
     */
    public function toArray(): array
    {
        return ['x' => $this->x, 'y' => $this->y, 'z' => $this->z];
    }

    public function toArr(): Arr
    {
        return new Arr($this->toArray());
    }

    public function __toString(): string
    {
        return sprintf('(%s, %s, %s)', $this->x, $this->y, $this->z);
    }
}
