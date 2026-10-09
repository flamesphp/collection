<?php

declare(strict_types=1);

namespace Flames\Collection;

use Flames\Collection\Trait\Prototype as PrototypeTrait;

/**
 * Bounded range: lower/upper values with inclusive/exclusive bounds.
 *
 * Driver-specific literal formats (e.g. PostgreSQL `[10,100)`) belong in the ORM cast layer.
 */
final readonly class Range implements \Stringable
{
    use PrototypeTrait;

    public function __construct(
        public mixed $lower,
        public mixed $upper,
        public bool $lowerInclusive = true,
        public bool $upperInclusive = false,
    ) {
    }

    public static function parse(mixed $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_array($value)) {
            return self::fromArray($value);
        }

        $string = trim((string) $value);

        if (str_starts_with($string, '{')) {
            $decoded = json_decode($string, true, flags: JSON_THROW_ON_ERROR);

            return self::parse($decoded);
        }

        throw new \InvalidArgumentException('Invalid range payload.');
    }

    /**
     * @param array{lower?:mixed,upper?:mixed,lowerInclusive?:bool,upperInclusive?:bool}|list<mixed> $value
     */
    public static function fromArray(array $value): self
    {
        if (isset($value['lower'], $value['upper'])) {
            return new self(
                $value['lower'],
                $value['upper'],
                (bool) ($value['lowerInclusive'] ?? true),
                (bool) ($value['upperInclusive'] ?? false),
            );
        }

        if (count($value) === 2) {
            return new self($value[0], $value[1], true, false);
        }

        throw new \InvalidArgumentException('Invalid range array payload.');
    }

    /**
     * @return array{lower:mixed,upper:mixed,lowerInclusive:bool,upperInclusive:bool}
     */
    public function toArray(): array
    {
        return [
            'lower'           => $this->lower,
            'upper'           => $this->upper,
            'lowerInclusive'  => $this->lowerInclusive,
            'upperInclusive'  => $this->upperInclusive,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function equals(self $other): bool
    {
        return $this->toArray() == $other->toArray();
    }
}
