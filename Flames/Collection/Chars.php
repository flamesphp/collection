<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Collection\Trait\Prototype as PrototypeTrait;

/**
 * Stateless utility class for single-character operations.
 *
 * Character values are UTF-8 strings containing exactly one code point.
 */
final class Chars
{
    use PrototypeTrait;

    private const string ENCODING = 'UTF-8';

    /**
     * Returns a single-character string parsed from $value.
     *
     * Integers are treated as Unicode code points. Strings must contain one code point.
     */
    public static function parse(mixed $value): string
    {
        if (is_int($value)) {
            return self::fromCodePoint($value);
        }

        $char = (string) $value;
        if ($char === '') {
            throw new \InvalidArgumentException('Character value cannot be empty.');
        }

        if (mb_strlen($char, self::ENCODING) !== 1) {
            throw new \InvalidArgumentException('Character value must contain exactly one code point.');
        }

        return $char;
    }

    public static function fromCodePoint(int $codePoint): string
    {
        if ($codePoint < 0) {
            throw new \InvalidArgumentException('Code point cannot be negative.');
        }

        if (function_exists('mb_chr')) {
            $char = mb_chr($codePoint, self::ENCODING);
            if ($char === false) {
                throw new \InvalidArgumentException(sprintf('Invalid code point: %d.', $codePoint));
            }
            return $char;
        }

        if ($codePoint > 0xFF) {
            throw new \InvalidArgumentException(sprintf('Code point %d requires mbstring.', $codePoint));
        }

        return chr($codePoint);
    }

    public static function toCodePoint(mixed $value): int
    {
        $char = self::parse($value);

        if (function_exists('mb_ord')) {
            $codePoint = mb_ord($char, self::ENCODING);
            if ($codePoint === false) {
                throw new \InvalidArgumentException('Unable to resolve code point.');
            }
            return $codePoint;
        }

        return ord($char[0]);
    }

    public static function isSingle(mixed $value): bool
    {
        if (is_int($value)) {
            return $value >= 0;
        }

        $char = (string) $value;
        return $char !== '' && mb_strlen($char, self::ENCODING) === 1;
    }

    public static function equals(mixed $value, mixed $other): bool
    {
        return self::parse($value) === self::parse($other);
    }

    public static function equalsIgnoreCase(mixed $value, mixed $other): bool
    {
        return mb_strtolower(self::parse($value), self::ENCODING)
            === mb_strtolower(self::parse($other), self::ENCODING);
    }

    public static function toUpper(mixed $value): string
    {
        return mb_strtoupper(self::parse($value), self::ENCODING);
    }

    public static function toLower(mixed $value): string
    {
        return mb_strtolower(self::parse($value), self::ENCODING);
    }

    public static function isUpper(mixed $value): bool
    {
        $char = self::parse($value);
        return $char === mb_strtoupper($char, self::ENCODING);
    }

    public static function isLower(mixed $value): bool
    {
        $char = self::parse($value);
        return $char === mb_strtolower($char, self::ENCODING);
    }

    public static function isAlpha(mixed $value): bool
    {
        return preg_match('/^\p{L}$/u', self::parse($value)) === 1;
    }

    public static function isAlphanumeric(mixed $value): bool
    {
        return preg_match('/^[\p{L}\p{N}]$/u', self::parse($value)) === 1;
    }

    public static function isDigit(mixed $value): bool
    {
        return preg_match('/^\p{Nd}$/u', self::parse($value)) === 1;
    }

    public static function isHexDigit(mixed $value): bool
    {
        return ctype_xdigit(self::parse($value));
    }

    public static function isWhitespace(mixed $value): bool
    {
        return preg_match('/^\s$/u', self::parse($value)) === 1;
    }

    public static function isPrintable(mixed $value): bool
    {
        return preg_match('/^\p{Print}$/u', self::parse($value)) === 1;
    }

    public static function isControl(mixed $value): bool
    {
        return preg_match('/^\p{C}$/u', self::parse($value)) === 1;
    }

    public static function isAscii(mixed $value): bool
    {
        $char = self::parse($value);
        return strlen($char) === 1 && mb_strlen($char, self::ENCODING) === 1;
    }

    public static function isPunctuation(mixed $value): bool
    {
        return preg_match('/^\p{P}$/u', self::parse($value)) === 1;
    }

    public static function toInt(mixed $value): int
    {
        if (!self::isDigit($value)) {
            throw new \InvalidArgumentException('Character is not a decimal digit.');
        }
        return (int) self::parse($value);
    }
}
