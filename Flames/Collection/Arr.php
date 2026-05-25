<?php

declare(strict_types=1);

namespace Flames\Collection;

/**
 * Typed, object-oriented array wrapper built on top of PHP's ArrayObject.
 *
 * Provides a fluent API for the most common array operations while preserving
 * the native array-access and foreach semantics.
 *
 * Virtual read-only properties resolved through offsetGet:
 *
 * @property-read int   $length  Alias for count().
 * @property-read int   $count   Number of elements in the collection.
 * @property-read mixed $first   First element, or null when empty.
 * @property-read mixed $last    Last element, or null when empty.
 */
final class Arr extends \ArrayObject
{
    /** Tracks the highest numeric key ever set, mirrors PHP's internal array pointer. */
    private int $autoKey = -1;

    /**
     * @param array|self|null $value  Initial data, or null for an empty collection.
     */
    public function __construct(mixed $value = null)
    {
        if (!is_array($value) && !($value instanceof self)) {
            $value = [];
        }

        parent::__construct($value, \ArrayObject::ARRAY_AS_PROPS);

        if (!empty($value)) {
            $this->autoKey = $this->getLastNumberKey() ?? -1;
        }
    }

    /**
     * Creates an Arr from a JSON string.
     *
     * Returns an empty Arr when the input cannot be decoded.
     */
    public static function fromJson(string $json): self
    {
        $decoded = json_decode($json, true);
        return new self(is_array($decoded) ? $decoded : []);
    }

    /**
     * Recursively converts a (possibly nested) object into an Arr.
     *
     * Only public properties are included (cast-to-array semantics).
     */
    public static function fromObject(object $object): self
    {
        return new self(self::parseObjectToArray($object));
    }

    private static function parseObjectToArray(mixed $value): array
    {
        $value = (array) $value;
        foreach ($value as &$child) {
            if (is_array($child) || is_object($child)) {
                $child = self::parseObjectToArray($child);
            }
        }
        return $value;
    }

    /**
     * Resolves virtual properties (length, count, first, last).
     * Uses match for a jump-table dispatch instead of sequential if-checks.
     */
    public function offsetGet(mixed $key): mixed
    {
        $k = (string) $key;
        return match ($k) {
            'length', 'count' => !parent::offsetExists($k) ? $this->count()    : parent::offsetGet($k),
            'first'           => !parent::offsetExists($k) ? $this->getFirst() : parent::offsetGet($k),
            'last'            => !parent::offsetExists($k) ? $this->getLast()  : parent::offsetGet($k),
            default           => parent::offsetExists($k)  ? parent::offsetGet($k) : null,
        };
    }

    /**
     * Auto-increments the integer key when appending (empty-key syntax).
     * Maintains an internal counter so appends are O(1) instead of O(n).
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $k = (string) $key;
        if ($k === '') {
            $k = (string) (++$this->autoKey);
        } elseif (is_numeric($k)) {
            $intKey = (int) $k;
            if ($intKey > $this->autoKey) {
                $this->autoKey = $intKey;
            }
        }
        parent::offsetSet($k, $value);
    }

    /**
     * Replaces the underlying storage and resets the auto-key counter.
     */
    public function exchangeArray(array|object $array): array
    {
        $old = parent::exchangeArray($array);
        $this->autoKey = $this->getLastNumberKey() ?? -1;
        return $old;
    }

    /**
     * Returns the number of elements (alias for count()).
     */
    public function length(): int
    {
        return $this->count();
    }

    /**
     * Returns true when the collection contains no elements.
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Returns true when an element identical (===) to $value exists.
     */
    public function contains(mixed $value): bool
    {
        return in_array($value, (array) $this, true);
    }

    /**
     * Returns true when the given key exists in the collection.
     */
    public function containsKey(mixed $key): bool
    {
        return $this->offsetExists((string) $key);
    }

    /**
     * Returns the key of the first element identical (===) to $value, or null.
     * Uses the C-level array_search for maximum throughput.
     */
    public function indexOf(mixed $value): int|string|null
    {
        $key = array_search($value, (array) $this, true);
        return $key !== false ? $key : null;
    }

    /**
     * Returns the key of the last element identical (===) to $value, or null.
     */
    public function lastIndexOf(mixed $value): int|string|null
    {
        $found = null;
        foreach ((array) $this as $key => $item) {
            if ($value === $item) {
                $found = $key;
            }
        }
        return $found;
    }

    /**
     * Searches for the first element satisfying $delegate and returns it.
     *
     * When $isKeyValue is false the delegate receives each value:
     *   fn(mixed $value): bool
     *
     * When $isKeyValue is true the delegate receives key + value and the
     * returned Arr has the shape ['key' => $k, 'value' => $v]:
     *   fn(mixed $key, mixed $value): bool
     */
    public function find(\Closure $delegate, bool $isKeyValue = false): mixed
    {
        $arr = (array) $this;

        if ($isKeyValue === false) {
            // Wrap to preserve strict === true semantics from the original API
            return array_find($arr, static fn($v) => $delegate($v) === true);
        }

        // Delegate signature is fn($key, $value); array_find_key uses fn($value, $key)
        $key = array_find_key($arr, static fn($v, $k) => $delegate($k, $v) === true);
        return $key !== null ? new self(['key' => $key, 'value' => $arr[$key]]) : null;
    }

    /**
     * Returns all keys as a new Arr.
     */
    public function getKeys(): self
    {
        return new self(array_keys((array) $this));
    }

    /**
     * Returns the highest numeric key currently present, or null when none exist.
     * Single-pass O(n) — avoids allocating an intermediate filtered array.
     */
    public function getLastNumberKey(): int|null
    {
        $max = null;
        foreach (array_keys((array) $this) as $key) {
            if (is_numeric($key)) {
                $k = (int) $key;
                if ($max === null || $k > $max) {
                    $max = $k;
                }
            }
        }
        return $max;
    }

    /**
     * Returns the first element, or null when the collection is empty.
     */
    public function getFirst(): mixed
    {
        $arr = (array) $this;
        if ($arr === []) {
            return null;
        }
        return parent::offsetGet((string) array_key_first($arr));
    }

    /**
     * Returns the last element, or null when the collection is empty.
     */
    public function getLast(): mixed
    {
        $arr = (array) $this;
        if ($arr === []) {
            return null;
        }
        return parent::offsetGet((string) array_key_last($arr));
    }

    /**
     * Appends $value to the collection.
     *
     * @param bool $canDuplicate When false the value is only added if it is not already present.
     */
    public function add(mixed $value, bool $canDuplicate = true): self
    {
        if ($canDuplicate === false && $this->contains($value)) {
            return $this;
        }
        $this[] = $value;
        return $this;
    }

    /**
     * Sets the element at the given key.
     */
    public function addKey(mixed $key, mixed $value): self
    {
        $this[(string) $key] = $value;
        return $this;
    }

    /**
     * Removes the element at the given key (no-op when the key does not exist).
     */
    public function removeKey(mixed $key): self
    {
        $k = (string) $key;
        if ($k !== '' && $this->offsetExists($k)) {
            $this->offsetUnset($k);
        }
        return $this;
    }

    /**
     * Removes all elements identical (===) to $value.
     */
    public function remove(mixed $value): self
    {
        foreach ((array) $this as $key => $item) {
            if ($value === $item) {
                parent::offsetUnset((string) $key);
            }
        }
        return $this;
    }

    /**
     * Removes all elements from the collection.
     */
    public function clear(): self
    {
        $this->exchangeArray([]);
        return $this;
    }

    /**
     * Returns a new Arr containing at most the first $limit elements.
     *
     * When $preserveKeys is true the original keys are kept; when false
     * the result is re-indexed starting from 0.
     */
    public function limit(int $limit, bool $preserveKeys = true): self
    {
        if ($limit <= 0) {
            return new self();
        }
        return new self(array_slice((array) $this, 0, $limit, $preserveKeys));
    }

    /**
     * Applies $delegate to every element and returns a new Arr with the results.
     *
     * Delegate signature: fn(mixed $value, mixed $key): mixed
     */
    public function map(\Closure $delegate): self
    {
        $arr    = (array) $this;
        $result = [];
        foreach ($arr as $key => $value) {
            $result[$key] = $delegate($value, $key);
        }
        return new self($result);
    }

    /**
     * Returns a new Arr containing only elements for which $delegate returns true.
     *
     * Delegate signature: fn(mixed $value, mixed $key): bool
     */
    public function filter(\Closure $delegate): self
    {
        $arr    = (array) $this;
        $result = [];
        foreach ($arr as $key => $value) {
            if ($delegate($value, $key) === true) {
                $result[$key] = $value;
            }
        }
        return new self($result);
    }

    /**
     * Iterates over every element, invoking $delegate with each value and key.
     *
     * Returns $this for chaining. Delegate signature: fn(mixed $value, mixed $key): void
     */
    public function each(\Closure $delegate): self
    {
        foreach ((array) $this as $key => $value) {
            $delegate($value, $key);
        }
        return $this;
    }

    /**
     * Sorts elements by value in ascending order (preserves key association).
     */
    public function sort(): self
    {
        $this->asort();
        return $this;
    }

    /**
     * Sorts elements by key in ascending order.
     */
    public function sortByKey(): self
    {
        $this->ksort();
        return $this;
    }

    /**
     * Sorts elements using a custom comparison closure.
     *
     * The delegate must return:
     *  -1 (or false) when $a < $b
     *   0 (or null)  when $a == $b
     *   1 (or true)  when $a > $b
     */
    public function sortByDelegate(\Closure $delegate): self
    {
        $this->uasort(static function (mixed $a, mixed $b) use ($delegate): int {
            $r = $delegate($a, $b);
            if ($r === false) return -1;
            if ($r === true)  return 1;
            if ($r === null)  return 0;
            return $r <=> 0;
        });
        return $this;
    }

    /**
     * Returns a new Arr with the elements in randomised order.
     */
    public function shuffle(): self
    {
        $array = $this->toArray();
        shuffle($array);
        return new self($array);
    }

    /**
     * Returns a new Arr with the elements in reversed order.
     *
     * @param bool $preserveKeys When true original keys are preserved.
     */
    public function reverse(bool $preserveKeys = true): self
    {
        return new self(array_reverse($this->toArray(), $preserveKeys));
    }

    /**
     * Joins all elements into a single string using $delimiter.
     *
     * Each element is cast to string before joining.
     */
    public function join(string $delimiter = ','): string
    {
        return implode($delimiter, $this->toArray());
    }

    /**
     * Merges another array or Arr into this collection and returns the result
     * as a new Arr (this instance is not modified).
     *
     * @param bool $replace When true uses array_replace_recursive (later values win).
     */
    public function merge(self|array|null $array = null, bool $replace = true): self
    {
        $base  = $this->toArray();
        $other = ($array instanceof self) ? $array->toArray() : ($array ?? []);

        return new self($replace
            ? array_replace_recursive($base, $other)
            : array_merge_recursive($base, $other)
        );
    }

    /**
     * Converts the collection (and nested Arr instances) to a plain PHP array.
     */
    public function toArray(): array
    {
        $array = $this->getArrayCopy();
        foreach ($array as &$value) {
            if ($value instanceof self) {
                $value = $value->toArray();
            }
        }
        return $array;
    }

    /**
     * Encodes the collection as a JSON string.
     * @throws \JsonException
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * Returns a deep clone of this instance.
     */
    public function clone(): self
    {
        return clone $this;
    }

    /**
     * @throws \JsonException
     */
    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
