<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Collection\Arr;

/**
 * Stateless utility class for common string operations.
 *
 * All methods accept mixed $value and cast it to string internally.
 * Multibyte-aware variants always assume UTF-8 encoding.
 */
final class Arrays
{
    public static function toArr(array $value): Arr
    {
        return new Arr($value);
    }
}
