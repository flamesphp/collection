<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Dumpper;
use Flames\Collection\Trait\Prototype as PrototypeTrait;
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
    use PrototypeTrait;

    /** Tracks the highest numeric key ever set, mirrors PHP's internal array pointer. */
    #[Dumpper\Attribute\Hidden]
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
     * Recursively iterates over every element (array_walk_recursive).
     *
     * Delegate signature: fn(mixed $value, mixed $key): void
     */
    public function eachRecursive(\Closure $delegate): self
    {
        $arr = (array) $this;
        array_walk_recursive($arr, static function (mixed $value, mixed $key) use ($delegate): void {
            $delegate($value, $key);
        });
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
        $other = self::toNative($array);

        return new self($replace
            ? array_replace_recursive($base, $other)
            : array_merge_recursive($base, $other)
        );
    }

    /**
     * Returns the element at $key, or $default when the key is absent.
     */
    public function get(mixed $key, mixed $default = null): mixed
    {
        $k = (string) $key;
        return parent::offsetExists($k) ? parent::offsetGet($k) : $default;
    }

    /**
     * Returns true when the collection is a list (sequential integer keys starting at 0).
     */
    public function isList(): bool
    {
        return array_is_list((array) $this);
    }

    /**
     * Returns true when at least one key is a non-integer string.
     */
    public function isAssociative(): bool
    {
        return !$this->isList() && $this->count() > 0;
    }

    /**
     * Returns a new Arr containing only the given keys.
     */
    public function only(self|array $keys): self
    {
        $result = [];
        foreach (self::toNative($keys) as $key) {
            $k = (string) $key;
            if (parent::offsetExists($k)) {
                $result[$k] = parent::offsetGet($k);
            }
        }
        return new self($result);
    }

    /**
     * Returns a new Arr excluding the given keys.
     */
    public function except(self|array $keys): self
    {
        $result = (array) $this;
        foreach (self::toNative($keys) as $key) {
            unset($result[(string) $key]);
        }
        return new self($result);
    }

    /**
     * Returns a new Arr with re-indexed values (array_values).
     */
    public function getValues(): self
    {
        return new self(array_values((array) $this));
    }

    /**
     * Returns a new Arr with keys and values swapped (array_flip).
     */
    public function flip(): self
    {
        return new self(array_flip((array) $this));
    }

    /**
     * Returns a new Arr with duplicate values removed (array_unique).
     */
    public function unique(int $flags = SORT_STRING): self
    {
        return new self(array_unique((array) $this, $flags));
    }

    /**
     * Returns a new Arr containing a slice (array_slice).
     */
    public function slice(int $offset, ?int $length = null, bool $preserveKeys = false): self
    {
        return new self(array_slice((array) $this, $offset, $length, $preserveKeys));
    }

    /**
     * Returns a new Arr of Arr chunks (array_chunk).
     */
    public function chunk(int $length, bool $preserveKeys = false): self
    {
        $chunks = array_chunk((array) $this, max(1, $length), $preserveKeys);
        return new self(array_map(static fn(array $chunk): self => new self($chunk), $chunks));
    }

    /**
     * Pads the collection to $size with $value (array_pad).
     */
    public function pad(int $size, mixed $value): self
    {
        return new self(array_pad((array) $this, $size, $value));
    }

    /**
     * Builds an associative Arr from these keys and the given values (array_combine).
     */
    public function combine(self|array $values): self
    {
        $combined = array_combine(array_values((array) $this), self::toNative($values));
        return new self($combined !== false ? $combined : []);
    }

    /**
     * Returns occurrence counts for each value (array_count_values).
     */
    public function countValues(): self
    {
        return new self(array_count_values((array) $this));
    }

    /**
     * Returns values from a column (array_column).
     */
    public function column(string|int|null $columnKey, string|int|null $indexKey = null): self
    {
        return new self(array_column((array) $this, $columnKey, $indexKey));
    }

    /**
     * Alias for column().
     */
    public function pluck(string|int|null $columnKey, string|int|null $indexKey = null): self
    {
        return $this->column($columnKey, $indexKey);
    }

    /**
     * Merges another array using array_merge (non-recursive, reindexes numeric keys).
     */
    public function concat(self|array $array): self
    {
        return new self(array_merge((array) $this, self::toNative($array)));
    }

    /**
     * Sums all numeric elements (array_sum).
     */
    public function sum(): int|float
    {
        return array_sum((array) $this);
    }

    /**
     * Multiplies all numeric elements (array_product).
     */
    public function product(): int|float
    {
        return array_product((array) $this);
    }

    /**
     * Reduces the collection to a single value (array_reduce).
     */
    public function reduce(\Closure $delegate, mixed $initial = null): mixed
    {
        return array_reduce((array) $this, $delegate, $initial);
    }

    /**
     * Returns true when every element satisfies $delegate (array_all).
     */
    public function every(\Closure $delegate): bool
    {
        return array_all((array) $this, static fn(mixed $value, mixed $key): bool => $delegate($value, $key) === true);
    }

    /**
     * Returns true when at least one element satisfies $delegate (array_any).
     */
    public function some(\Closure $delegate): bool
    {
        return array_any((array) $this, static fn(mixed $value, mixed $key): bool => $delegate($value, $key) === true);
    }

    /**
     * Returns one or more random keys (array_rand).
     *
     * @return int|string|self  A single key, or an Arr of keys when $num > 1.
     */
    public function random(int $num = 1): mixed
    {
        $arr = (array) $this;
        if ($arr === []) {
            return $num === 1 ? null : new self();
        }

        if ($num === 1) {
            return array_rand($arr);
        }

        return new self(array_rand($arr, min($num, count($arr))));
    }

    /**
     * Returns a random element value.
     */
    public function randomValue(): mixed
    {
        $key = $this->random();
        return $key === null ? null : parent::offsetGet((string) $key);
    }

    /**
     * Replaces values from another array (array_replace).
     */
    public function replace(self|array $array): self
    {
        return new self(array_replace((array) $this, self::toNative($array)));
    }

    /**
     * Returns values present in this collection but not in $arrays (array_diff).
     */
    public function diff(self|array ...$arrays): self
    {
        $others = array_map(static fn(self|array $array): array => self::toNative($array), $arrays);
        return new self(array_diff((array) $this, ...$others));
    }

    /**
     * Returns values present in this collection but not in $arrays, comparing keys and values (array_diff_assoc).
     */
    public function diffAssoc(self|array ...$arrays): self
    {
        $others = array_map(static fn(self|array $array): array => self::toNative($array), $arrays);
        return new self(array_diff_assoc((array) $this, ...$others));
    }

    /**
     * Returns values whose keys are not present in $arrays (array_diff_key).
     */
    public function diffKey(self|array ...$arrays): self
    {
        $others = array_map(static fn(self|array $array): array => self::toNative($array), $arrays);
        return new self(array_diff_key((array) $this, ...$others));
    }

    /**
     * Returns values present in this collection but not in $arrays,
     * comparing keys with $delegate (array_diff_uassoc).
     *
     * Delegate signature: fn(mixed $keyA, mixed $keyB): int
     */
    public function diffAssocByKeyDelegate(\Closure $delegate, self|array ...$arrays): self
    {
        $others = array_map(static fn(self|array $array): array => self::toNative($array), $arrays);
        return new self(self::callArrayFuncWithDelegate('array_diff_uassoc', (array) $this, $others, $delegate));
    }

    /**
     * Returns values whose keys are not present in $arrays,
     * comparing keys with $delegate (array_diff_ukey).
     *
     * Delegate signature: fn(mixed $keyA, mixed $keyB): int
     */
    public function diffKeysByDelegate(\Closure $delegate, self|array ...$arrays): self
    {
        $others = array_map(static fn(self|array $array): array => self::toNative($array), $arrays);
        return new self(self::callArrayFuncWithDelegate('array_diff_ukey', (array) $this, $others, $delegate));
    }

    /**
     * Returns values present in this collection but not in $arrays,
     * comparing values with $delegate (array_udiff).
     *
     * Delegate signature: fn(mixed $valueA, mixed $valueB): int
     */
    public function diffByDelegate(\Closure $delegate, self|array ...$arrays): self
    {
        $others = array_map(static fn(self|array $array): array => self::toNative($array), $arrays);
        return new self(self::callArrayFuncWithDelegate('array_udiff', (array) $this, $others, $delegate));
    }

    /**
     * Returns values present in this collection but not in $arrays,
     * comparing keys and values with $delegate (array_udiff_assoc).
     *
     * Delegate signature: fn(mixed $valueA, mixed $valueB): int
     */
    public function diffAssocByDelegate(\Closure $delegate, self|array ...$arrays): self
    {
        $others = array_map(static fn(self|array $array): array => self::toNative($array), $arrays);
        return new self(self::callArrayFuncWithDelegate('array_udiff_assoc', (array) $this, $others, $delegate));
    }

    /**
     * Returns values whose keys are not present in $arrays,
     * comparing keys with $delegate (array_udiff_key).
     *
     * Delegate signature: fn(mixed $keyA, mixed $keyB): int
     */
    public function diffKeyByDelegate(\Closure $delegate, self|array ...$arrays): self
    {
        $others = array_map(static fn(self|array $array): array => self::toNative($array), $arrays);
        return new self(self::callArrayFuncWithDelegate('array_udiff_key', (array) $this, $others, $delegate));
    }

    /**
     * Returns values present in this collection and all $arrays (array_intersect).
     */
    public function intersect(self|array ...$arrays): self
    {
        $others = array_map(static fn(self|array $array): array => self::toNative($array), $arrays);
        return new self(array_intersect((array) $this, ...$others));
    }

    /**
     * Returns values present in this collection and all $arrays, comparing keys and values (array_intersect_assoc).
     */
    public function intersectAssoc(self|array ...$arrays): self
    {
        $others = array_map(static fn(self|array $array): array => self::toNative($array), $arrays);
        return new self(array_intersect_assoc((array) $this, ...$others));
    }

    /**
     * Returns values whose keys are present in this collection and all $arrays (array_intersect_key).
     */
    public function intersectKey(self|array ...$arrays): self
    {
        $others = array_map(static fn(self|array $array): array => self::toNative($array), $arrays);
        return new self(array_intersect_key((array) $this, ...$others));
    }

    /**
     * Returns values present in this collection and all $arrays,
     * comparing keys with $delegate (array_intersect_uassoc).
     *
     * Delegate signature: fn(mixed $keyA, mixed $keyB): int
     */
    public function intersectAssocByKeyDelegate(\Closure $delegate, self|array ...$arrays): self
    {
        $others = array_map(static fn(self|array $array): array => self::toNative($array), $arrays);
        return new self(self::callArrayFuncWithDelegate('array_intersect_uassoc', (array) $this, $others, $delegate));
    }

    /**
     * Returns values whose keys are present in this collection and all $arrays,
     * comparing keys with $delegate (array_intersect_ukey).
     *
     * Delegate signature: fn(mixed $keyA, mixed $keyB): int
     */
    public function intersectKeysByDelegate(\Closure $delegate, self|array ...$arrays): self
    {
        $others = array_map(static fn(self|array $array): array => self::toNative($array), $arrays);
        return new self(self::callArrayFuncWithDelegate('array_intersect_ukey', (array) $this, $others, $delegate));
    }

    /**
     * Returns values present in this collection and all $arrays,
     * comparing values with $delegate (array_uintersect).
     *
     * Delegate signature: fn(mixed $valueA, mixed $valueB): int
     */
    public function intersectByDelegate(\Closure $delegate, self|array ...$arrays): self
    {
        $others = array_map(static fn(self|array $array): array => self::toNative($array), $arrays);
        return new self(self::callArrayFuncWithDelegate('array_uintersect', (array) $this, $others, $delegate));
    }

    /**
     * Returns values present in this collection and all $arrays,
     * comparing keys and values with $delegate (array_uintersect_assoc).
     *
     * Delegate signature: fn(mixed $valueA, mixed $valueB): int
     */
    public function intersectAssocByDelegate(\Closure $delegate, self|array ...$arrays): self
    {
        $others = array_map(static fn(self|array $array): array => self::toNative($array), $arrays);
        return new self(self::callArrayFuncWithDelegate('array_uintersect_assoc', (array) $this, $others, $delegate));
    }

    /**
     * Returns values whose keys are present in this collection and all $arrays,
     * comparing keys with $delegate (array_uintersect_ukey).
     *
     * Delegate signature: fn(mixed $keyA, mixed $keyB): int
     */
    public function intersectKeyByDelegate(\Closure $delegate, self|array ...$arrays): self
    {
        $others = array_map(static fn(self|array $array): array => self::toNative($array), $arrays);
        return new self(self::callArrayFuncWithDelegate('array_uintersect_ukey', (array) $this, $others, $delegate));
    }

    /**
     * Returns a new Arr with keys converted to lower/upper case (array_change_key_case).
     */
    public function changeKeyCase(int $case = CASE_LOWER): self
    {
        return new self(array_change_key_case((array) $this, $case));
    }

    /**
     * Flattens nested arrays/Arr up to $depth levels.
     */
    public function flatten(int $depth = PHP_INT_MAX): self
    {
        $result = [];
        self::flattenInto($result, (array) $this, $depth);
        return new self($result);
    }

    /**
     * Groups elements by a key returned from $delegate or taken from a property name.
     */
    public function groupBy(\Closure|string $key): self
    {
        $groups = [];
        foreach ((array) $this as $itemKey => $item) {
            $groupKey = is_string($key) ? self::readProperty($item, $key) : $key($item, $itemKey);
            $groups[(string) $groupKey][] = $item;
        }
        foreach ($groups as $groupKey => $items) {
            $groups[$groupKey] = new self($items);
        }
        return new self($groups);
    }

    /**
     * Builds an associative Arr keyed by $delegate result or property name.
     */
    public function keyBy(\Closure|string $key): self
    {
        $result = [];
        foreach ((array) $this as $itemKey => $item) {
            $newKey = is_string($key) ? self::readProperty($item, $key) : $key($item, $itemKey);
            $result[(string) $newKey] = $item;
        }
        return new self($result);
    }

    /**
     * Appends one or more values (array_push).
     */
    public function push(mixed ...$values): self
    {
        foreach ($values as $value) {
            $this[] = $value;
        }
        return $this;
    }

    /**
     * Removes and returns the last element (array_pop).
     */
    public function pop(): mixed
    {
        $arr = (array) $this;
        if ($arr === []) {
            return null;
        }
        $key = array_key_last($arr);
        $value = parent::offsetGet((string) $key);
        parent::offsetUnset((string) $key);
        return $value;
    }

    /**
     * Removes and returns the first element (array_shift).
     */
    public function shift(): mixed
    {
        $arr = (array) $this;
        if ($arr === []) {
            return null;
        }
        $key = array_key_first($arr);
        $value = parent::offsetGet((string) $key);
        parent::offsetUnset((string) $key);
        return $value;
    }

    /**
     * Prepends one or more values (array_unshift).
     */
    public function unshift(mixed ...$values): self
    {
        $merged = [...$values, ...(array) $this];
        $this->exchangeArray($merged);
        return $this;
    }

    /**
     * Removes and/or replaces a sequence of elements (array_splice).
     */
    public function splice(int $offset, ?int $length = null, mixed ...$replacement): self
    {
        $arr = (array) $this;
        array_splice($arr, $offset, $length, $replacement);
        $this->exchangeArray($arr);
        return $this;
    }

    /**
     * Sorts by value in descending order, preserving keys (arsort).
     */
    public function sortDesc(): self
    {
        $this->arsort();
        return $this;
    }

    /**
     * Sorts by value in ascending order and re-indexes from zero (sort).
     */
    public function sortIndexed(): self
    {
        $arr = (array) $this;
        sort($arr);
        $this->exchangeArray($arr);
        return $this;
    }

    /**
     * Sorts by value in descending order and re-indexes from zero (rsort).
     */
    public function sortIndexedDesc(): self
    {
        $arr = (array) $this;
        rsort($arr);
        $this->exchangeArray($arr);
        return $this;
    }

    /**
     * Sorts by key in descending order (krsort).
     */
    public function sortByKeyDesc(): self
    {
        $this->krsort();
        return $this;
    }

    /**
     * Sorts by key using a custom comparison closure (uksort).
     */
    public function sortByKeyDelegate(\Closure $delegate): self
    {
        $this->uksort(static function (mixed $a, mixed $b) use ($delegate): int {
            $r = $delegate($a, $b);
            if ($r === false) return -1;
            if ($r === true)  return 1;
            if ($r === null)  return 0;
            return $r <=> 0;
        });
        return $this;
    }

    /**
     * Natural-order sort by value, preserving keys (natsort / natcasesort).
     */
    public function sortNatural(bool $caseInsensitive = false): self
    {
        $caseInsensitive ? $this->natcasesort() : $this->natsort();
        return $this;
    }

    /**
     * Sorts this collection with explicit order and type flags (array_multisort).
     */
    public function multisort(int $order = SORT_ASC, int $flags = SORT_REGULAR): self
    {
        $arr = (array) $this;
        array_multisort($arr, $order, $flags);
        $this->exchangeArray($arr);
        return $this;
    }

    /**
     * Sorts multiple columns in parallel (array_multisort).
     *
     * Each column definition: [self|array $data, int $order = SORT_ASC, int $flags = SORT_REGULAR]
     * Arr inputs are updated in place. Returns the first column as Arr.
     *
     * @param list<array{0: self|array, 1?: int, 2?: int}> $columns
     */
    public static function multisortColumns(array $columns): self
    {
        if ($columns === []) {
            return new self();
        }

        $refs      = [];
        $instances = [];
        $args      = [];

        foreach ($columns as $index => $column) {
            $refs[$index] = self::toNative($column[0]);
            if ($column[0] instanceof self) {
                $instances[$index] = $column[0];
            }

            $args[] = &$refs[$index];
            if (isset($column[1])) {
                $args[] = $column[1];
            }
            if (isset($column[2])) {
                $args[] = $column[2];
            }
        }

        array_multisort(...$args);

        foreach ($instances as $index => $instance) {
            $instance->exchangeArray($refs[$index]);
        }

        $first = $columns[0][0];
        return $first instanceof self ? $first : new self($refs[0]);
    }

    /**
     * Creates an Arr containing a range of values (range).
     */
    public static function range(int|float $start, int|float $end, int|float $step = 1): self
    {
        return new self(range($start, $end, $step));
    }

    /**
     * Creates an Arr filled with $count copies of $value (array_fill).
     */
    public static function fill(int $count, mixed $value): self
    {
        return new self(array_fill(0, max(0, $count), $value));
    }

    /**
     * Creates an Arr with the given keys all mapped to $value (array_fill_keys).
     */
    public static function fillKeys(self|array $keys, mixed $value): self
    {
        return new self(array_fill_keys(self::toNative($keys), $value));
    }

    /**
     * @param array|self|null $value
     */
    private static function toNative(self|array|null $value): array
    {
        if ($value instanceof self) {
            return $value->toArray();
        }
        return $value ?? [];
    }

    /**
     * @param array<int, array> $others
     */
    private static function callArrayFuncWithDelegate(string $function, array $array, array $others, \Closure $delegate): array
    {
        return $function(...array_merge([$array], $others, [$delegate]));
    }

    private static function readProperty(mixed $item, string $key): mixed
    {
        if (is_array($item)) {
            return $item[$key] ?? null;
        }
        if ($item instanceof self) {
            return $item->get($key);
        }
        if (is_object($item)) {
            return $item->$key ?? null;
        }
        return null;
    }

    /**
     * @param array<int|string, mixed> $target
     */
    private static function flattenInto(array &$target, array $array, int $depth): void
    {
        foreach ($array as $value) {
            if ($depth > 0 && (is_array($value) || $value instanceof self)) {
                self::flattenInto($target, $value instanceof self ? $value->toArray() : $value, $depth - 1);
                continue;
            }
            $target[] = $value;
        }
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
