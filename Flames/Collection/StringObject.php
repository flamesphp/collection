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
            'length', 'count' => strlen($this->value),
            default           => null,
        };
    }

    public function length(bool $multibyte = false): int
    {
        return $multibyte ? mb_strlen($this->value, 'UTF-8') : strlen($this->value);
    }

    public function count(bool $multibyte = false): int
    {
        return $multibyte ? mb_strlen($this->value, 'UTF-8') : strlen($this->value);
    }

    public function isEmpty(): bool
    {
        return $this->value === '';
    }

    public function toLower(bool $multibyte = false): self
    {
        $this->value = $multibyte ? mb_strtolower($this->value, 'UTF-8') : strtolower($this->value);
        return $this;
    }

    public function toUpper(bool $multibyte = false): self
    {
        $this->value = $multibyte ? mb_strtoupper($this->value, 'UTF-8') : strtoupper($this->value);
        return $this;
    }

    /**
     * Capitalises the first letter of each word (mutating).
     */
    public function capitalize(): self
    {
        $this->value = mb_convert_case($this->value, MB_CASE_TITLE, 'UTF-8');
        return $this;
    }

    /**
     * Capitalises only the very first character (mutating).
     */
    public function capitalizeFirst(): self
    {
        if ($this->value !== '') {
            $this->value = mb_strtoupper(mb_substr($this->value, 0, 1, 'UTF-8'), 'UTF-8')
                         . mb_substr($this->value, 1, null, 'UTF-8');
        }
        return $this;
    }

    public function startsWith(mixed $needle, bool $caseSensitive = true): bool
    {
        $search = (string) $needle;
        if ($caseSensitive === false) {
            return str_starts_with(mb_strtolower($this->value, 'UTF-8'), mb_strtolower($search, 'UTF-8'));
        }
        return str_starts_with($this->value, $search);
    }

    public function endsWith(mixed $needle, bool $caseSensitive = true): bool
    {
        $search = (string) $needle;
        if ($caseSensitive === false) {
            return str_ends_with(mb_strtolower($this->value, 'UTF-8'), mb_strtolower($search, 'UTF-8'));
        }
        return str_ends_with($this->value, $search);
    }

    public function contains(mixed $needle, bool $caseSensitive = true): bool
    {
        $search = (string) $needle;
        if ($caseSensitive === false) {
            return str_contains(mb_strtolower($this->value, 'UTF-8'), mb_strtolower($search, 'UTF-8'));
        }
        return str_contains($this->value, $search);
    }

    public function containsAny(array|Arr $needles, bool $caseSensitive = true): bool
    {
        return Strings::containsAny($this->value, $needles, $caseSensitive);
    }

    public function equals(mixed $needle, bool $caseSensitive = true): bool
    {
        $search = (string) $needle;
        if ($caseSensitive === false) {
            return mb_strtolower($this->value, 'UTF-8') === mb_strtolower($search, 'UTF-8');
        }
        return $this->value === $search;
    }

    public function equalsAny(array|Arr $needles, bool $caseSensitive = true): bool
    {
        return Strings::equalsAny($this->value, $needles, $caseSensitive);
    }

    public function replace(mixed $needle, mixed $replace): self
    {
        $this->value = str_replace((string) $needle, (string) $replace, $this->value);
        return $this;
    }

    public function remove(mixed $needle): self
    {
        $this->value = str_replace((string) $needle, '', $this->value);
        return $this;
    }

    /**
     * Appends $suffix to the wrapped value (mutating).
     */
    public function append(mixed $suffix): self
    {
        $this->value .= (string) $suffix;
        return $this;
    }

    /**
     * Prepends $prefix to the wrapped value (mutating).
     */
    public function prepend(mixed $prefix): self
    {
        $this->value = (string) $prefix . $this->value;
        return $this;
    }

    /**
     * Pads the value to $length (mutating).
     *
     * Use STR_PAD_LEFT, STR_PAD_RIGHT (default), or STR_PAD_BOTH for $type.
     */
    public function pad(int $length, string $pad = ' ', int $type = STR_PAD_RIGHT): self
    {
        $this->value = str_pad($this->value, $length, $pad, $type);
        return $this;
    }

    public function encode(bool $raw = false): self
    {
        $this->value = $raw ? urlencode($this->value) : rawurlencode($this->value);
        return $this;
    }

    public function decode(bool $raw = false): self
    {
        $this->value = $raw ? urldecode($this->value) : rawurldecode($this->value);
        return $this;
    }

    public function split(string $delimiter = ',', bool $clearEmpty = true, bool $keepDelimiter = false): Arr
    {
        return Strings::split($this->value, $delimiter, $clearEmpty, $keepDelimiter);
    }

    public function splitLength(mixed $length): Arr
    {
        $chunks = str_split($this->value, max(1, (int) $length));
        return new Arr($chunks !== false ? $chunks : []);
    }

    public function splitWords(): Arr
    {
        return new Arr(explode(' ', $this->value));
    }

    public function splitLines(): Arr
    {
        return new Arr(explode("\n", str_replace(["\r\n", "\r"], "\n", $this->value)));
    }

    public function sub(mixed $start, mixed $length = null): self
    {
        $this->value = $length !== null
            ? substr($this->value, (int) $start, (int) $length)
            : substr($this->value, (int) $start);
        return $this;
    }

    public function indexOf(mixed $needle, bool $caseSensitive = true): int|null
    {
        $pos = $caseSensitive
            ? strpos($this->value, (string) $needle)
            : stripos($this->value, (string) $needle);
        return $pos !== false ? $pos : null;
    }

    public function lastIndexOf(mixed $needle, bool $caseSensitive = true): int|null
    {
        $pos = $caseSensitive
            ? strrpos($this->value, (string) $needle)
            : strripos($this->value, (string) $needle);
        return $pos !== false ? $pos : null;
    }

    public function trim(mixed $charList = null, bool $multibyte = false): self
    {
        if ($charList === null) {
            $this->value = trim($this->value);
            return $this;
        }
        $chars = (string) $charList;
        if ($multibyte) {
            $escaped = str_replace('/', '\/', preg_quote($chars));
            $this->value = (string) preg_replace("/(^[$escaped]+)|([$escaped]+$)/us", '', $this->value);
        } else {
            $this->value = trim($this->value, $chars);
        }
        return $this;
    }

    public function addSlashes(): self
    {
        $this->value = addslashes($this->value);
        return $this;
    }

    public function removeSlashes(): self
    {
        $this->value = stripslashes($this->value);
        return $this;
    }

    public function toBase64(): self
    {
        $this->value = base64_encode($this->value);
        return $this;
    }

    /**
     * Creates a new StringObject from a Base64-encoded string.
     *
     * Returns null when the input is not valid Base64.
     */
    public static function fromBase64(mixed $value): self|null
    {
        $decoded = base64_decode((string) $value, strict: true);
        return $decoded !== false ? new self($decoded) : null;
    }

    /**
     * Encodes the value as hexadecimal (mutating).
     */
    public function toHex(): self
    {
        $this->value = bin2hex($this->value);
        return $this;
    }

    /**
     * Creates a new StringObject from a hexadecimal string.
     *
     * Returns null when the input is not valid hex.
     */
    public static function fromHex(mixed $value): self|null
    {
        $decoded = hex2bin((string) $value);
        return $decoded !== false ? new self($decoded) : null;
    }

    public function getOnlyNumbers(mixed $whitelist = ''): self
    {
        $escaped = preg_quote((string) $whitelist, '/');
        $this->value = (string) preg_replace("/[^0-9{$escaped}]/", '', $this->value);
        return $this;
    }

    public function getOnlyLetters(): self
    {
        $this->value = (string) preg_replace('/[^a-zA-Z]+/', '', $this->value);
        return $this;
    }

    public function getOnlyLettersAndNumbers(): self
    {
        $this->value = (string) preg_replace('/[^a-zA-Z0-9]+/', '', $this->value);
        return $this;
    }

    /**
     * Removes all whitespace characters (mutating).
     */
    public function removeSpaces(bool $includeLineBreaks = true): self
    {
        $str     = trim($this->value);
        $pattern = $includeLineBreaks ? '/[ \t\r\n]+/' : '/[ \t]+/';
        $this->value = (string) preg_replace($pattern, '', $str);
        return $this;
    }

    /**
     * Removes common special characters (mutating).
     */
    public function removeSpecialCharacters(bool $includeUnderline = true): self
    {
        $ul      = $includeUnderline ? '_' : '';
        $pattern = '/[-`~!@#$%^&*' . $ul . '()+={}\\[\\]\\\\|;:\'",.><?\/]+/';
        $this->value = (string) preg_replace($pattern, '', $this->value);
        return $this;
    }

    /**
     * Removes all digit characters (mutating).
     */
    public function removeNumbers(): self
    {
        $this->value = (string) preg_replace('/[0-9]+/', '', $this->value);
        return $this;
    }

    /**
     * Strips all HTML and PHP tags (mutating).
     */
    public function stripHtmlTags(bool $cleanContent = false): self
    {
        if ($cleanContent) {
            $str = str_replace(['<', '>'], [' <', '> '], $this->value);
            $str = strip_tags($str);
            $str = (string) preg_replace('/\s+/', ' ', $str);
            $this->value = trim($str);
        } else {
            $this->value = strip_tags($this->value);
        }
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
        $this->value = substr($this->value, 0, (int) $limit);
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
        return (int) $this->value;
    }

    public function toFloat(): float
    {
        return (float) $this->value;
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
