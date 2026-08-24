<?php

declare(strict_types=1);

namespace LaraCeemes\Fields;

use Illuminate\Contracts\Container\Container;
use LaraCeemes\Contracts\FieldType;
use LaraCeemes\Exceptions\FieldTypeAlreadyRegistered;
use LaraCeemes\Exceptions\UnknownFieldType;

final class FieldRegistry
{
    /** @var array<string, class-string<FieldType>|FieldType> */
    private array $types = [];

    public function __construct(private readonly Container $container) {}

    /** @param class-string<FieldType>|FieldType $fieldType */
    public function register(string|FieldType $fieldType, bool $replace = false): self
    {
        $instance = $this->resolve($fieldType);
        $handle = $instance->handle();

        if (! $replace && isset($this->types[$handle])) {
            throw FieldTypeAlreadyRegistered::forHandle($handle);
        }

        $this->types[$handle] = $fieldType;

        return $this;
    }

    public function has(string $handle): bool
    {
        return isset($this->types[$handle]);
    }

    public function get(string $handle): FieldType
    {
        if (! $this->has($handle)) {
            throw UnknownFieldType::forHandle($handle);
        }

        return $this->resolve($this->types[$handle]);
    }

    /** @return array<string, FieldType> */
    public function all(): array
    {
        $resolved = [];

        foreach (array_keys($this->types) as $handle) {
            $resolved[$handle] = $this->get($handle);
        }

        return $resolved;
    }

    /** @param class-string<FieldType>|FieldType $fieldType */
    private function resolve(string|FieldType $fieldType): FieldType
    {
        if ($fieldType instanceof FieldType) {
            return $fieldType;
        }

        return $this->container->make($fieldType);
    }
}
