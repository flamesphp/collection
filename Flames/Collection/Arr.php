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
    /**
     * @param array|self|null $value  Initial data, or null for an empty collection.
     */
    public function __construct(mixed $value = null)
    {
        if (!is_array($value) && !($value instanceof self)) {
            $value = [];
        }

        parent::__construct($value, \ArrayObject::ARRAY_AS_PROPS);
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
     */
    public function offsetGet(mixed $key): mixed
    {
        $k = (string) $key;

        if (($k === 'length' || $k === 'count') && !parent::offsetExists($k)) {
            return $this->count();
        }
        if ($k === 'first' && !parent::offsetExists($k)) {
            return $this->getFirst();
        }
        if ($k === 'last' && !parent::offsetExists($k)) {
            return $this->getLast();
        }

        return @parent::offsetGet($k);
    }

    /**
     * Auto-increments the integer key when appending (empty-key syntax).
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $k = (string) $key;
        if ($k === '') {
            $last = $this->getLastNumberKey();
            $k    = (string) ($last === null ? 0 : $last + 1);
        }
        parent::offsetSet($k, $value);
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
        foreach ($this as $item) {
            if ($value === $item) {
                return true;
            }
        }
        return false;
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
     */
    public function indexOf(mixed $value): int|string|null
    {
        foreach ($this as $key => $item) {
            if ($value === $item) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Returns the key of the last element identical (===) to $value, or null.
     */
    public function lastIndexOf(mixed $value): int|string|null
    {
        $found = null;
        foreach ($this as $key => $item) {
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
        if ($isKeyValue === false) {
            foreach ($this as $value) {
                if ($delegate($value) === true) {
                    return $value;
                }
            }
            return null;
        }

        foreach ($this as $key => $value) {
            if ($delegate($key, $value) === true) {
                return new self(['key' => $key, 'value' => $value]);
            }
        }
        return null;
    }

    /**
     * Returns all keys as a new Arr.
     */
    public function getKeys(): self
    {
        return new self(array_keys((array) $this));
    }

    /**
     * Returns the highest numeric key present, or null when none exist.
     */
    public function getLastNumberKey(): int|null
    {
        $top  = null;
        foreach (array_keys((array) $this) as $key) {
            if (is_numeric($key)) {
                $int = (int) $key;
                if ($top === null || $int > $top) {
                    $top = $int;
                }
            }
        }
        return $top;
    }

    /**
     * Returns the first element, or null when the collection is empty.
     */
    public function getFirst(): mixed
    {
        if ($this->count() === 0) {
            return null;
        }
        $keys = array_keys((array) $this);
        return parent::offsetGet($keys[0]);
    }

    /**
     * Returns the last element, or null when the collection is empty.
     */
    public function getLast(): mixed
    {
        $count = $this->count();
        if ($count === 0) {
            return null;
        }
        $keys = array_keys((array) $this);
        return parent::offsetGet($keys[$count - 1]);
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
        if ($k !== '' && ($this->offsetExists($k) || isset($this[$k]))) {
            unset($this[$k]);
        }
        return $this;
    }

    /**
     * Removes all elements identical (===) to $value.
     */
    public function remove(mixed $value): self
    {
        foreach ($this as $key => $item) {
            if ($value === $item) {
                unset($this[$key]);
            }
        }
        return $this;
    }

    /**
     * Removes all elements from the collection.
     */
    public function clear(): self
    {
        foreach (array_keys((array) $this) as $key) {
            unset($this[$key]);
        }
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

        $result = new self();
        $count  = 0;
        foreach ($this as $key => $value) {
            if ($count >= $limit) {
                break;
            }
            $preserveKeys ? $result[$key] = $value : $result[] = $value;
            $count++;
        }
        return $result;
    }

    /**
     * Applies $delegate to every element and returns a new Arr with the results.
     *
     * Delegate signature: fn(mixed $value, mixed $key): mixed
     */
    public function map(\Closure $delegate): self
    {
        $result = new self();
        foreach ($this as $key => $value) {
            $result[$key] = $delegate($value, $key);
        }
        return $result;
    }

    /**
     * Returns a new Arr containing only elements for which $delegate returns true.
     *
     * Delegate signature: fn(mixed $value, mixed $key): bool
     */
    public function filter(\Closure $delegate): self
    {
        $result = new self();
        foreach ($this as $key => $value) {
            if ($delegate($value, $key) === true) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Iterates over every element, invoking $delegate with each value and key.
     *
     * Returns $this for chaining. Delegate signature: fn(mixed $value, mixed $key): void
     */
    public function each(\Closure $delegate): self
    {
        foreach ($this as $key => $value) {
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
            return match (true) {
                $r < 0  => -1,
                $r > 0  => 1,
                default => 0,
            };
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

    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
