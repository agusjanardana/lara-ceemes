<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

use LaraCeemes\Contracts\FieldType;

abstract class AbstractFieldType implements FieldType
{
    public function rules(array $config = []): array
    {
        return [$this->presenceRule($config)];
    }

    public function normalize(mixed $value, array $config = []): mixed
    {
        return $value;
    }

    public function serialize(mixed $value, array $config = []): mixed
    {
        return $this->normalize($value, $config);
    }

    public function resolve(mixed $value, array $config = []): mixed
    {
        return $value;
    }

    public function adminView(): string
    {
        return 'ceemes::fields.base';
    }

    /** @param array<string, mixed> $config */
    protected function presenceRule(array $config): string
    {
        return ($config['required'] ?? false) === true ? 'required' : 'nullable';
    }
}
