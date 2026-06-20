<?php

declare(strict_types=1);

namespace Flames\Collection\Prototype;

use ArrayAccess;
use BadMethodCallException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Proxy passed as the first argument to DYNAMIC and STATIC prototype handlers.
 *
 * Methods and properties follow the same visibility rules:
 *   • private  — always blocked
 *   • protected — allowed only when the host class is not final
 *   • public   — always allowed
 *
 * Reflection metadata is cached per class member for the lifetime of the process.
 *
 * @internal
 */
final class Delegate implements ArrayAccess
{
    /** @var array<string, ReflectionMethod> */
    private static array $methodCache = [];

    /** @var array<string, ReflectionProperty> */
    private static array $propertyCache = [];

    /** @var array<class-string, bool> */
    private static array $finalClassCache = [];

    public function __construct(private object $instance)
    {
    }

    public function __call(string $name, array $arguments): mixed
    {
        $method = $this->resolveMethod($name);

        return $method->invoke($this->instance, ...$arguments);
    }

    public function __get(string $name): mixed
    {
        $property = $this->resolveProperty($name);

        return $property->getValue($this->instance);
    }

    public function __set(string $name, mixed $value): void
    {
        $property = $this->resolveProperty($name);

        $property->setValue($this->instance, $value);
    }

    private function resolveMethod(string $name): ReflectionMethod
    {
        $class = $this->instance::class;
        $key   = $class . '::' . $name;

        if (isset(self::$methodCache[$key])) {
            return self::$methodCache[$key];
        }

        try {
            $method = new ReflectionMethod($this->instance, $name);
        } catch (\ReflectionException) {
            throw new BadMethodCallException(sprintf(
                'Method "%s" does not exist on %s.',
                $name,
                $class
            ));
        }

        $this->assertMemberAccessible($method->isPrivate(), $method->isProtected(), $class, $name, 'method');

        if ($method->isProtected()) {
            $method->setAccessible(true);
        }

        return self::$methodCache[$key] = $method;
    }

    private function resolveProperty(string $name): ReflectionProperty
    {
        $class = $this->instance::class;
        $key   = $class . '::$' . $name;

        if (isset(self::$propertyCache[$key])) {
            return self::$propertyCache[$key];
        }

        try {
            $property = new ReflectionProperty($this->instance, $name);
        } catch (\ReflectionException) {
            throw new BadMethodCallException(sprintf(
                'Property "%s" does not exist on %s.',
                $name,
                $class
            ));
        }

        $this->assertMemberAccessible($property->isPrivate(), $property->isProtected(), $class, $name, 'property');

        if ($property->isProtected()) {
            $property->setAccessible(true);
        }

        return self::$propertyCache[$key] = $property;
    }

    private function assertMemberAccessible(
        bool $isPrivate,
        bool $isProtected,
        string $class,
        string $name,
        string $kind
    ): void {
        if ($isPrivate) {
            throw new BadMethodCallException(sprintf(
                'Prototype cannot access private %s %s::$%s.',
                $kind,
                $class,
                $name
            ));
        }

        if ($this->isFinalClass($class) && $isProtected) {
            throw new BadMethodCallException(sprintf(
                'Prototype cannot access protected %s %s::$%s on a final class.',
                $kind,
                $class,
                $name
            ));
        }
    }

    private function isFinalClass(string $class): bool
    {
        return self::$finalClassCache[$class] ??= (new ReflectionClass($class))->isFinal();
    }

    public function offsetExists(mixed $offset): bool
    {
        $this->assertArrayAccess();

        return $this->instance->offsetExists($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        $this->assertArrayAccess();

        return $this->instance->offsetGet($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->assertArrayAccess();

        $this->instance->offsetSet($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->assertArrayAccess();

        $this->instance->offsetUnset($offset);
    }

    private function assertArrayAccess(): void
    {
        if (!$this->instance instanceof ArrayAccess) {
            throw new BadMethodCallException(sprintf(
                '%s does not support array access.',
                $this->instance::class
            ));
        }
    }
}
