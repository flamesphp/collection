<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Collection\Trait\Prototype as PrototypeTrait;

/**
 * Stateless utility class for object and value-type introspection.
 */
final class Objects
{
    use PrototypeTrait;

    public static function isObject(mixed $value): bool
    {
        return is_object($value);
    }

    public static function isNull(mixed $value): bool
    {
        return $value === null;
    }

    public static function isScalar(mixed $value): bool
    {
        return is_scalar($value);
    }

    public static function isArray(mixed $value): bool
    {
        return is_array($value);
    }

    public static function isCallable(mixed $value): bool
    {
        return is_callable($value);
    }

    public static function isResource(mixed $value): bool
    {
        return is_resource($value);
    }

    public static function getType(mixed $value): string
    {
        return get_debug_type($value);
    }

    public static function className(object $value): string
    {
        return $value::class;
    }

    public static function shortName(object $value): string
    {
        $class = $value::class;
        $position = strrpos($class, '\\');
        return $position === false ? $class : substr($class, $position + 1);
    }

    public static function namespace(object $value): string|null
    {
        $class = $value::class;
        $position = strrpos($class, '\\');
        return $position === false ? null : substr($class, 0, $position);
    }

    public static function instanceOf(object $value, string $class): bool
    {
        return $value instanceof $class;
    }

    public static function isInstanceOf(mixed $value, string $class): bool
    {
        return is_object($value) && $value instanceof $class;
    }

    public static function isA(mixed $value, string $class): bool
    {
        return is_a($value, $class, true);
    }

    public static function hasProperty(object $value, string $property): bool
    {
        return property_exists($value, $property);
    }

    public static function getProperty(object $value, string $property): mixed
    {
        if (!property_exists($value, $property)) {
            throw new \InvalidArgumentException(sprintf(
                'Property "%s" does not exist on %s.',
                $property,
                $value::class,
            ));
        }

        return $value->{$property};
    }

    public static function setProperty(object $value, string $property, mixed $propertyValue): void
    {
        $value->{$property} = $propertyValue;
    }

    public static function hasMethod(object $value, string $method): bool
    {
        return method_exists($value, $method);
    }

    public static function isMethodCallable(object $value, string $method): bool
    {
        return is_callable([$value, $method]);
    }

    public static function clone(object $value): object
    {
        return clone $value;
    }

    public static function cloneDeep(object $value): object
    {
        return unserialize(serialize($value));
    }

    public static function equals(mixed $value, mixed $other): bool
    {
        if ($value === $other) {
            return true;
        }

        if (!is_object($value) || !is_object($other)) {
            return false;
        }

        return $value == $other;
    }

    public static function identical(mixed $value, mixed $other): bool
    {
        return $value === $other;
    }

    public static function hash(object $value): string
    {
        return spl_object_hash($value);
    }

    public static function id(object $value): int
    {
        return spl_object_id($value);
    }
}
