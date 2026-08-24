<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

abstract class AbstractStringField extends AbstractFieldType
{
    public function rules(array $config = []): array
    {
        $rules = [$this->presenceRule($config), 'string'];

        if (isset($config['max_length'])) {
            $rules[] = 'max:'.(int) $config['max_length'];
        }

        return $rules;
    }

    public function normalize(mixed $value, array $config = []): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim((string) $value);
    }
}
