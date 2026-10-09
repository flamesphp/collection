<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Collection\Trait\Prototype as PrototypeTrait;

/**
 * RFC 4122 UUID (cross-driver: PostgreSQL, MySQL, MongoDB, …).
 */
final readonly class Uuid implements \Stringable
{
    use PrototypeTrait;

    public function __construct(public string $value)
    {
        self::validate($this->value);
    }

    public static function parse(mixed $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return new self(strtolower(trim((string) $value)));
    }

    public static function v4(): self
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        $hex     = bin2hex($data);

        return new self(sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        ));
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private static function validate(string $value): void
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $value)) {
            throw new \InvalidArgumentException('Invalid UUID value.');
        }
    }
}
