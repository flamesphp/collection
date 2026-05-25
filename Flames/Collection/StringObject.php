<?php

declare(strict_types=1);

namespace Flames\Collection;

/**
 * Fluent, mutable wrapper around a single PHP string.
 *
 * Mutating methods return $this so calls can be chained:
 *
 * ```php
 * $result = (new StringObject('  Hello World!  '))
 *     ->trim()
 *     ->toLower()
 *     ->replace('hello', 'hi')
 *     ->toString();
 * ```
 *
 * Non-mutating methods (startsWith, contains, indexOf, …) return their
 * result directly and do not modify the wrapped value.
 *
 * @property-read int $length  Character count of the current value.
 * @property-read int $count   Alias for $length.
 */
final class StringObject
{
    private string $value;

    public function __construct(mixed $value = null)
    {
        $this->value = (string) $value;
    }

    public function __get(string $name): mixed
    {
        return match (strtolower($name)) {
            'length', 'count' => $this->length(),
            default           => null,
        };
    }

    public function length(bool $multibyte = false): int
    {
        return Strings::length($this->value, $multibyte);
    }

    public function count(bool $multibyte = false): int
    {
        return Strings::count($this->value, $multibyte);
    }

    public function isEmpty(): bool
    {
        return $this->value === '';
    }

    public function toLower(bool $multibyte = false): self
    {
        $this->value = Strings::toLower($this->value, $multibyte);
        return $this;
    }

    public function toUpper(bool $multibyte = false): self
    {
        $this->value = Strings::toUpper($this->value, $multibyte);
        return $this;
    }

    /**
     * Capitalises the first letter of each word (mutating).
     */
    public function capitalize(): self
    {
        $this->value = Strings::capitalize($this->value);
        return $this;
    }

    /**
     * Capitalises only the very first character (mutating).
     */
    public function capitalizeFirst(): self
    {
        $this->value = Strings::capitalizeFirst($this->value);
        return $this;
    }

    public function startsWith(mixed $needle, bool $caseSensitive = true): bool
    {
        return Strings::startsWith($this->value, $needle, $caseSensitive);
    }

    public function endsWith(mixed $needle, bool $caseSensitive = true): bool
    {
        return Strings::endsWith($this->value, $needle, $caseSensitive);
    }

    public function contains(mixed $needle, bool $caseSensitive = true): bool
    {
        return Strings::contains($this->value, $needle, $caseSensitive);
    }

    public function containsAny(array|Arr $needles, bool $caseSensitive = true): bool
    {
        return Strings::containsAny($this->value, $needles, $caseSensitive);
    }

    public function equals(mixed $needle, bool $caseSensitive = true): bool
    {
        return Strings::equals($this->value, $needle, $caseSensitive);
    }

    public function equalsAny(array|Arr $needles, bool $caseSensitive = true): bool
    {
        return Strings::equalsAny($this->value, $needles, $caseSensitive);
    }

    public function replace(mixed $needle, mixed $replace): self
    {
        $this->value = Strings::replace($this->value, $needle, $replace);
        return $this;
    }

    public function remove(mixed $needle): self
    {
        $this->value = Strings::remove($this->value, $needle);
        return $this;
    }

    /**
     * Appends $suffix to the wrapped value (mutating).
     */
    public function append(mixed $suffix): self
    {
        $this->value = Strings::append($this->value, $suffix);
        return $this;
    }

    /**
     * Prepends $prefix to the wrapped value (mutating).
     */
    public function prepend(mixed $prefix): self
    {
        $this->value = Strings::prepend($this->value, $prefix);
        return $this;
    }

    /**
     * Pads the value to $length (mutating).
     *
     * Use STR_PAD_LEFT, STR_PAD_RIGHT (default), or STR_PAD_BOTH for $type.
     */
    public function pad(int $length, string $pad = ' ', int $type = STR_PAD_RIGHT): self
    {
        $this->value = Strings::pad($this->value, $length, $pad, $type);
        return $this;
    }

    public function encode(bool $raw = false): self
    {
        $this->value = Strings::encode($this->value, $raw);
        return $this;
    }

    public function decode(bool $raw = false): self
    {
        $this->value = Strings::decode($this->value, $raw);
        return $this;
    }

    public function split(string $delimiter = ',', bool $clearEmpty = true, bool $keepDelimiter = false): Arr
    {
        return Strings::split($this->value, $delimiter, $clearEmpty, $keepDelimiter);
    }

    public function splitLength(mixed $length): Arr
    {
        return Strings::splitLength($this->value, $length);
    }

    public function splitWords(): Arr
    {
        return Strings::splitWords($this->value);
    }

    public function splitLines(): Arr
    {
        return Strings::splitLines($this->value);
    }

    public function sub(mixed $start, mixed $length = null): self
    {
        $this->value = Strings::sub($this->value, $start, $length);
        return $this;
    }

    public function indexOf(mixed $needle, bool $caseSensitive = true): int|null
    {
        return Strings::indexOf($this->value, $needle, $caseSensitive);
    }

    public function lastIndexOf(mixed $needle, bool $caseSensitive = true): int|null
    {
        return Strings::lastIndexOf($this->value, $needle, $caseSensitive);
    }

    public function trim(mixed $charList = null, bool $multibyte = false): self
    {
        $this->value = Strings::trim($this->value, $charList, $multibyte);
        return $this;
    }

    public function addSlashes(): self
    {
        $this->value = Strings::addSlashes($this->value);
        return $this;
    }

    public function removeSlashes(): self
    {
        $this->value = Strings::removeSlashes($this->value);
        return $this;
    }

    public function toBase64(): self
    {
        $this->value = Strings::toBase64($this->value);
        return $this;
    }

    /**
     * Creates a new StringObject from a Base64-encoded string.
     *
     * Returns null when the input is not valid Base64.
     */
    public static function fromBase64(mixed $value): self|null
    {
        $decoded = Strings::fromBase64($value);
        return $decoded !== null ? new self($decoded) : null;
    }

    /**
     * Encodes the value as hexadecimal (mutating).
     */
    public function toHex(): self
    {
        $this->value = Strings::toHex($this->value);
        return $this;
    }

    /**
     * Creates a new StringObject from a hexadecimal string.
     *
     * Returns null when the input is not valid hex.
     */
    public static function fromHex(mixed $value): self|null
    {
        $decoded = Strings::fromHex($value);
        return $decoded !== null ? new self($decoded) : null;
    }

    public function getOnlyNumbers(mixed $whitelist = ''): self
    {
        $this->value = Strings::getOnlyNumbers($this->value, $whitelist);
        return $this;
    }

    public function getOnlyLetters(): self
    {
        $this->value = Strings::getOnlyLetters($this->value);
        return $this;
    }

    public function getOnlyLettersAndNumbers(): self
    {
        $this->value = Strings::getOnlyLettersAndNumbers($this->value);
        return $this;
    }

    /**
     * Removes all whitespace characters (mutating).
     */
    public function removeSpaces(bool $includeLineBreaks = true): self
    {
        $this->value = Strings::removeSpaces($this->value, $includeLineBreaks);
        return $this;
    }

    /**
     * Removes common special characters (mutating).
     */
    public function removeSpecialCharacters(bool $includeUnderline = true): self
    {
        $this->value = Strings::removeSpecialCharacters($this->value, $includeUnderline);
        return $this;
    }

    /**
     * Removes all digit characters (mutating).
     */
    public function removeNumbers(): self
    {
        $this->value = Strings::removeNumbers($this->value);
        return $this;
    }

    /**
     * Strips all HTML and PHP tags (mutating).
     */
    public function stripHtmlTags(bool $cleanContent = false): self
    {
        $this->value = Strings::stripHtmlTags($this->value, $cleanContent);
        return $this;
    }

    /**
     * Truncates the value to at most $limit characters (mutating).
     *
     * @param bool $wordBoundary Never cut mid-word when true.
     */
    public function truncate(int $limit, bool $wordBoundary = false): self
    {
        $this->value = Strings::truncate($this->value, $limit, $wordBoundary);
        return $this;
    }

    /**
     * Limits the value to $limit bytes (mutating).
     */
    public function limit(mixed $limit = 10): self
    {
        $this->value = Strings::limit($this->value, $limit);
        return $this;
    }

    /**
     * Converts the value to a URL-friendly slug (mutating).
     */
    public function toSlug(string $delimiter = '-'): self
    {
        $this->value = Strings::toSlug($this->value, $delimiter);
        return $this;
    }

    /**
     * Removes accented / diacritic characters (mutating).
     */
    public function removeAccents(): self
    {
        $this->value = Strings::removeAccents($this->value);
        return $this;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function toInt(): int
    {
        return Ints::parse($this->value);
    }

    public function toFloat(): float
    {
        return Floats::parse($this->value);
    }

    public function toBool(): bool|null
    {
        return Bools::parse($this->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
