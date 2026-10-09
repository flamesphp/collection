<?php

declare(strict_types=1);

namespace Flames\Collection;

/**
 * Read-only {@see Arr}. Data is fixed at construction time.
 *
 * @property-read int   $length
 * @property-read int   $count
 * @property-read mixed $first
 * @property-read mixed $last
 */
final class ArrImmutable extends Arr
{
    /**
     * @param array|Arr|self|null $value
     */
    public function __construct(mixed $value = null)
    {
        parent::__construct(self::normalize($value));
    }

    public static function fromJson(string $json): self
    {
        $decoded = json_decode($json, true);

        return new self(is_array($decoded) ? $decoded : []);
    }

    protected function guardMutable(): never
    {
        throw new \LogicException('ArrImmutable is readonly.');
    }

    public function toMutable(): Arr
    {
        $normalized = [];

        foreach ($this->getArrayCopy() as $key => $child) {
            if ($child instanceof self) {
                $normalized[$key] = $child->toMutable();
                continue;
            }

            if ($child instanceof Arr) {
                $normalized[$key] = $child;
                continue;
            }

            $normalized[$key] = $child;
        }

        return new Arr($normalized);
    }

    /**
     * @param array|Arr|self|null $value
     *
     * @return array<int|string, mixed>
     */
    private static function normalize(mixed $value): array
    {
        if ($value instanceof self) {
            $value = $value->getArrayCopy();
        } elseif ($value instanceof Arr) {
            $value = $value->getArrayCopy();
        } elseif (!is_array($value)) {
            $value = [];
        }

        $normalized = [];
        foreach ($value as $key => $child) {
            if (is_array($child) || $child instanceof Arr) {
                $normalized[$key] = new self($child);
                continue;
            }

            $normalized[$key] = $child;
        }

        return $normalized;
    }
}
