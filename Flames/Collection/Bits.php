<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Collection\Trait\Prototype as PrototypeTrait;

/**
 * Stateless utility class for common bitwise operations.
 *
 * Bit positions are zero-based from the least significant bit (LSB = 0).
 */
final class Bits
{
    use PrototypeTrait;

    public static function parse(mixed $value): int
    {
        return (int) $value;
    }

    public static function and(int $value, int $mask): int
    {
        return $value & $mask;
    }

    public static function or(int $value, int $mask): int
    {
        return $value | $mask;
    }

    public static function xor(int $value, int $mask): int
    {
        return $value ^ $mask;
    }

    public static function not(int $value): int
    {
        return ~$value;
    }

    /**
     * Returns a mask with a single bit set at $position.
     */
    public static function single(int $position): int
    {
        return 1 << $position;
    }

    /**
     * Returns a mask with the lowest $width bits set (e.g. width 8 → 0b11111111).
     */
    public static function mask(int $width): int
    {
        if ($width <= 0) {
            return 0;
        }
        if ($width >= PHP_INT_SIZE * 8) {
            return -1;
        }
        return (1 << $width) - 1;
    }

    public static function set(int $value, int $position): int
    {
        return $value | self::single($position);
    }

    public static function clear(int $value, int $position): int
    {
        return $value & ~self::single($position);
    }

    public static function toggle(int $value, int $position): int
    {
        return $value ^ self::single($position);
    }

    public static function test(int $value, int $position): bool
    {
        return ($value & self::single($position)) !== 0;
    }

    public static function setMask(int $value, int $mask): int
    {
        return $value | $mask;
    }

    public static function clearMask(int $value, int $mask): int
    {
        return $value & ~$mask;
    }

    public static function toggleMask(int $value, int $mask): int
    {
        return $value ^ $mask;
    }

    /**
     * Returns true when every bit set in $mask is also set in $value.
     */
    public static function hasMask(int $value, int $mask): bool
    {
        return ($value & $mask) === $mask;
    }

    /**
     * Returns true when $value and $mask share at least one bit.
     */
    public static function hasAny(int $value, int $mask): bool
    {
        return ($value & $mask) !== 0;
    }

    public static function shiftLeft(int $value, int $amount): int
    {
        return $value << $amount;
    }

    public static function shiftRight(int $value, int $amount): int
    {
        return $value >> $amount;
    }

    public static function rotateLeft(int $value, int $amount, int $width = PHP_INT_SIZE * 8): int
    {
        if ($width <= 0) {
            return 0;
        }
        $amount %= $width;
        if ($amount === 0) {
            return self::truncate($value, $width);
        }
        $value = self::truncate($value, $width);
        $mask  = self::mask($width);
        return (($value << $amount) & $mask) | ($value >> ($width - $amount));
    }

    public static function rotateRight(int $value, int $amount, int $width = PHP_INT_SIZE * 8): int
    {
        if ($width <= 0) {
            return 0;
        }
        $amount %= $width;
        if ($amount === 0) {
            return self::truncate($value, $width);
        }
        $value = self::truncate($value, $width);
        $mask  = self::mask($width);
        return (($value >> $amount) & $mask) | (($value << ($width - $amount)) & $mask);
    }

    /**
     * Counts the number of set bits (population count).
     */
    public static function count(int $value): int
    {
        $count = 0;
        while ($value !== 0) {
            $value &= $value - 1;
            $count++;
        }
        return $count;
    }

    /**
     * Returns the number of bits required to represent $value in binary (0 → 0).
     */
    public static function length(int $value): int
    {
        if ($value === 0) {
            return 0;
        }
        return (int) floor(log($value, 2)) + 1;
    }

    /**
     * Returns the zero-based index of the lowest set bit, or null when $value is 0.
     */
    public static function lowest(int $value): int|null
    {
        if ($value === 0) {
            return null;
        }
        return (int) log($value & -$value, 2);
    }

    /**
     * Returns the zero-based index of the highest set bit, or null when $value is 0.
     */
    public static function highest(int $value): int|null
    {
        if ($value === 0) {
            return null;
        }
        return self::length($value) - 1;
    }

    /**
     * Extracts $length bits starting at $offset (LSB = bit 0).
     */
    public static function extract(int $value, int $offset, int $length): int
    {
        if ($length <= 0) {
            return 0;
        }
        return ($value >> $offset) & self::mask($length);
    }

    /**
     * Inserts the lowest $length bits of $segment into $value at $offset.
     */
    public static function insert(int $value, int $segment, int $offset, int $length): int
    {
        if ($length <= 0) {
            return $value;
        }
        $segment = $segment & self::mask($length);
        $clear   = ~(self::mask($length) << $offset);
        return ($value & $clear) | ($segment << $offset);
    }

    /**
     * Keeps only the lowest $width bits of $value.
     */
    public static function truncate(int $value, int $width): int
    {
        return $value & self::mask($width);
    }

    /**
     * Encodes $value as a binary string, optionally left-padded to $pad bits.
     */
    public static function toBinary(int $value, ?int $pad = null): string
    {
        $binary = decbin($value);
        if ($pad !== null && $pad > strlen($binary)) {
            return str_pad($binary, $pad, '0', STR_PAD_LEFT);
        }
        return $binary;
    }

    public static function fromBinary(mixed $value): int
    {
        return bindec((string) $value);
    }

    /**
     * Encodes $value as a hexadecimal string, optionally left-padded to $pad nibbles.
     */
    public static function toHex(int $value, ?int $pad = null): string
    {
        $hex = dechex($value);
        if ($pad !== null && $pad > strlen($hex)) {
            return str_pad($hex, $pad, '0', STR_PAD_LEFT);
        }
        return $hex;
    }

    public static function fromHex(mixed $value): int
    {
        return hexdec((string) $value);
    }
}
