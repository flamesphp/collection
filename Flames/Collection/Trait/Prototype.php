<?php

declare(strict_types=1);

namespace Flames\Collection\Trait;

use BadMethodCallException;
use Closure;
use Flames\Collection\Arrays;
use Flames\Collection\Bools;
use Flames\Collection\Enum\Prototype\Type;
use Flames\Collection\Floats;
use Flames\Collection\Functions;
use Flames\Collection\Ints;
use Flames\Collection\Prototype\Delegate;
use Flames\Collection\Strings;

/**
 * Registers and dispatches custom method prototypes through __call / __callStatic.
 *
 * STATIC prototypes are dispatched via __callStatic — there is no automatic $this.
 *
 * Two common patterns:
 *
 *   1. Pure static (no instance): Classe::proto($arg1, …) — the handler receives only
 *      the arguments you pass (e.g. subset: Strings::toLower($stringValue)).
 *
 *   2. Static dispatch with explicit instance: Classe::proto($instance, …) — you pass the
 *      object as the first call argument; it is wrapped in {@see Delegate} when invoked.
 *      Prefer DYNAMIC ($instance->proto(…)) when the call site always has an instance.
 *
 * Scalar/primitive first arguments are never wrapped (collection utilities via subset).
 *
 * DYNAMIC prototypes are dispatched via __call. The host instance is always injected
 * automatically as the first handler argument, wrapped in {@see Delegate}.
 */
trait Prototype
{
    /** @var array<string, array<string, Closure>> */
    private static array $__prototypes__ = [];

    public static function prototype(string $name, Closure $handler, ?Type $type = null): void
    {
        $type ??= static::__defaultPrototypeType__();

        self::$__prototypes__[$name][$type->name] = $handler;
    }

    public static function hasPrototype(string $name, ?Type $type = null): bool
    {
        if (!isset(self::$__prototypes__[$name])) {
            return false;
        }

        if ($type === null) {
            return self::$__prototypes__[$name] !== [];
        }

        return isset(self::$__prototypes__[$name][$type->name]);
    }

    public static function __callStatic(string $name, array $arguments): mixed
    {
        $handler = self::$__prototypes__[$name][Type::STATIC->name] ?? null;

        if ($handler === null) {
            throw new BadMethodCallException(sprintf(
                'Static prototype "%s" is not defined on %s.',
                $name,
                static::class
            ));
        }

        return static::__invokePrototypeHandler__($handler, $arguments);
    }

    public function __call(string $name, array $arguments): mixed
    {
        $handler = self::$__prototypes__[$name][Type::DYNAMIC->name] ?? null;

        if ($handler === null) {
            throw new BadMethodCallException(sprintf(
                'Dynamic prototype "%s" is not defined on %s.',
                $name,
                static::class
            ));
        }

        return static::__invokePrototypeHandler__($handler, [$this, ...$arguments]);
    }

    protected static function __defaultPrototypeType__(): Type
    {
        return match (static::class) {
            Arrays::class,
            Bools::class,
            Floats::class,
            Functions::class,
            Ints::class,
            Strings::class => Type::STATIC,
            default        => Type::DYNAMIC,
        };
    }

    /**
     * @param  array<int, mixed> $arguments
     */
    private static function __invokePrototypeHandler__(Closure $handler, array $arguments): mixed
    {
        if (isset($arguments[0]) && is_object($arguments[0])) {
            $arguments[0] = new Delegate($arguments[0]);
        }

        return $handler(...$arguments);
    }
}
