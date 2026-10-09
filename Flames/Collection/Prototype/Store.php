<?php

declare(strict_types=1);

namespace Flames\Collection\Prototype;

use Closure;

/**
 * External store for {@see \Flames\Collection\Trait\Prototype} handlers.
 *
 * Keeps mutable state out of readonly value-object classes.
 */
final class Store
{
    /** @var array<class-string, array<string, array<string, Closure>>> */
    private static array $handlers = [];

    /**
     * @return array<string, array<string, Closure>>
     */
    public static function &for(string $class): array
    {
        self::$handlers[$class] ??= [];

        return self::$handlers[$class];
    }
}
