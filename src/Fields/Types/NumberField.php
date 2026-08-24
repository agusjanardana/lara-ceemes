<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

final class NumberField extends AbstractFieldType
{
    public function handle(): string
    {
        return 'number';
    }

    public function rules(array $config = []): array
    {
        $rules = [$this->presenceRule($config), ($config['integer'] ?? false) ? 'integer' : 'numeric'];

        if (isset($config['min'])) {
            $rules[] = 'min:'.$config['min'];
        }

        if (isset($config['max'])) {
            $rules[] = 'max:'.$config['max'];
        }

        return $rules;
    }

    public function normalize(mixed $value, array $config = []): int|float|null
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        if (($config['integer'] ?? false) === true) {
            return (int) $value;
        }

        $numeric = (float) $value;

        return floor($numeric) === $numeric ? (int) $numeric : $numeric;
    }
}
