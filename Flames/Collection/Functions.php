<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Ready\ResetData;

final class Functions
{
    public static function once(\Closure $function): mixed
    {
        if (ResetData::$weakMap->offsetExists($function)) {
            return self::$cache[$function];
        }

        ResetData::$weakMap[$function] = $function();
        return ResetData::$weakMap[$function];
    }
}
