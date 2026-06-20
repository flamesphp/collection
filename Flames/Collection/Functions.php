<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Collection\Trait\Prototype as PrototypeTrait;
use Flames\Ready\ResetData;

final class Functions
{
    use PrototypeTrait;

    public static function once(\Closure $function): mixed
    {
        if (ResetData::$weakMap->offsetExists($function)) {
            return ResetData::$weakMap[$function];
        }

        ResetData::$weakMap[$function] = $function();
        return ResetData::$weakMap[$function];
    }
}
