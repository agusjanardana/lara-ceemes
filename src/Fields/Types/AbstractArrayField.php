<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

abstract class AbstractArrayField extends AbstractFieldType
{
    public function rules(array $config = []): array
    {
        return [$this->presenceRule($config), 'array'];
    }

    /** @return array<array-key, mixed>|null */
    public function normalize(mixed $value, array $config = []): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_array($value) ? $value : null;
    }
}
