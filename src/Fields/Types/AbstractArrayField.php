<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

abstract class AbstractArrayField extends AbstractFieldType
{
    public function rules(array $config = []): array
    {
        $rules = [$this->presenceRule($config), 'array'];

        if (isset($config['min_items'])) {
            $rules[] = 'min:'.(int) $config['min_items'];
        }

        if (isset($config['max_items'])) {
            $rules[] = 'max:'.(int) $config['max_items'];
        }

        return $rules;
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
