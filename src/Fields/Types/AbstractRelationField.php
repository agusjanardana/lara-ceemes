<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

abstract class AbstractRelationField extends AbstractFieldType
{
    public function rules(array $config = []): array
    {
        if ($this->allowsMultiple($config)) {
            return [$this->presenceRule($config), 'array'];
        }

        return [$this->presenceRule($config), 'uuid'];
    }

    public function normalize(mixed $value, array $config = []): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($this->allowsMultiple($config)) {
            return array_values(array_unique(array_filter((array) $value)));
        }

        return (string) $value;
    }

    /** @param array<string, mixed> $config */
    protected function allowsMultiple(array $config): bool
    {
        return ($config['multiple'] ?? false) === true;
    }
}
