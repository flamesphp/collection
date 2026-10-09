<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Collection\Trait\Prototype as PrototypeTrait;

/**
 * Stateless utility class for binary string operations.
 */
final class Bytes
{
    use PrototypeTrait;

    public static function parse(mixed $value): string
    {
        return (string) $value;
    }

    public static function length(mixed $value): int
    {
        return strlen((string) $value);
    }

    public static function isEmpty(mixed $value): bool
    {
        return (string) $value === '';
    }

    public static function equals(mixed $value, mixed $other): bool
    {
        return hash_equals((string) $value, (string) $other);
    }

    public static function compare(mixed $value, mixed $other): int
    {
        return strcmp((string) $value, (string) $other);
    }

    public static function concat(mixed $value, mixed ...$others): string
    {
        return (string) $value . implode('', array_map(static fn (mixed $part): string => (string) $part, $others));
    }

    public static function slice(mixed $value, int $offset, ?int $length = null): string
    {
        return $length === null
            ? substr((string) $value, $offset)
            : substr((string) $value, $offset, $length);
    }

    public static function at(mixed $value, int $index): int
    {
        $bytes = (string) $value;
        $length = strlen($bytes);
        if ($index < 0) {
            $index += $length;
        }
        if ($index < 0 || $index >= $length) {
            throw new \OutOfRangeException(sprintf('Byte index %d is out of range.', $index));
        }
        return ord($bytes[$index]);
    }

    public static function fromByte(int $byte): string
    {
        if ($byte < 0 || $byte > 255) {
            throw new \InvalidArgumentException('Byte value must be between 0 and 255.');
        }
        return chr($byte);
    }

    /**
     * @param list<int> $bytes
     */
    public static function fromArray(array $bytes): string
    {
        $binary = '';
        foreach ($bytes as $byte) {
            $binary .= self::fromByte((int) $byte);
        }
        return $binary;
    }

    /**
     * @return list<int>
     */
    public static function toArray(mixed $value): array
    {
        return array_map(ord(...), str_split((string) $value));
    }

    public static function toArr(mixed $value): Arr
    {
        return new Arr(self::toArray($value));
    }

    public static function toHex(mixed $value, bool $uppercase = false): string
    {
        $hex = bin2hex((string) $value);
        return $uppercase ? strtoupper($hex) : $hex;
    }

    public static function fromHex(mixed $value): string
    {
        $hex = (string) $value;
        if ($hex === '') {
            return '';
        }
        if (strlen($hex) % 2 !== 0) {
            throw new \InvalidArgumentException('Hex string must have an even length.');
        }
        $binary = hex2bin($hex);
        if ($binary === false) {
            throw new \InvalidArgumentException('Invalid hex string.');
        }
        return $binary;
    }

    public static function toBase64(mixed $value): string
    {
        return base64_encode((string) $value);
    }

    public static function fromBase64(mixed $value, bool $strict = false): string
    {
        $decoded = base64_decode((string) $value, $strict);
        if ($decoded === false) {
            throw new \InvalidArgumentException('Invalid base64 string.');
        }
        return $decoded;
    }

    public static function toBase64Url(mixed $value): string
    {
        return rtrim(strtr(base64_encode((string) $value), '+/', '-_'), '=');
    }

    public static function fromBase64Url(mixed $value): string
    {
        $base64 = strtr((string) $value, '-_', '+/');
        $padding = strlen($base64) % 4;
        if ($padding !== 0) {
            $base64 .= str_repeat('=', 4 - $padding);
        }
        return self::fromBase64($base64, true);
    }

    /**
     * @param list<mixed> $values
     */
    public static function pack(string $format, array $values): string
    {
        return pack($format, ...$values);
    }

    /**
     * @return list<mixed>
     */
    public static function unpack(string $format, mixed $value, int $offset = 0): array
    {
        $result = unpack($format, (string) $value, $offset);
        if ($result === false) {
            throw new \InvalidArgumentException('Unable to unpack binary data.');
        }
        return $result;
    }

    public static function startsWith(mixed $value, mixed $prefix): bool
    {
        $bytes = (string) $value;
        $search = (string) $prefix;
        if ($search === '') {
            return true;
        }
        return str_starts_with($bytes, $search);
    }

    public static function endsWith(mixed $value, mixed $suffix): bool
    {
        $bytes = (string) $value;
        $search = (string) $suffix;
        if ($search === '') {
            return true;
        }
        return str_ends_with($bytes, $search);
    }

    public static function indexOf(mixed $value, mixed $needle, int $offset = 0): int|false
    {
        return strpos((string) $value, (string) $needle, $offset);
    }

    public static function repeat(mixed $value, int $times): string
    {
        if ($times < 0) {
            throw new \InvalidArgumentException('Repeat count cannot be negative.');
        }
        return str_repeat((string) $value, $times);
    }

    public static function padLeft(mixed $value, int $length, mixed $pad = "\0"): string
    {
        return str_pad((string) $value, $length, (string) $pad, STR_PAD_LEFT);
    }

    public static function padRight(mixed $value, int $length, mixed $pad = "\0"): string
    {
        return str_pad((string) $value, $length, (string) $pad, STR_PAD_RIGHT);
    }
}
