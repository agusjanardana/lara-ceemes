<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

use Illuminate\Validation\Rule;

final class SelectField extends AbstractFieldType
{
    public function handle(): string
    {
        return 'select';
    }

    public function rules(array $config = []): array
    {
        return [
            $this->presenceRule($config),
            Rule::in($this->optionValues($config)),
        ];
    }

    public function normalize(mixed $value, array $config = []): mixed
    {
        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, int|string>
     */
    private function optionValues(array $config): array
    {
        $options = is_array($config['options'] ?? null) ? $config['options'] : [];

        if (! array_is_list($options)) {
            return array_keys($options);
        }

        return array_values(array_filter(array_map(
            static fn (mixed $option): int|string|null => is_array($option)
                ? ($option['value'] ?? null)
                : (is_int($option) || is_string($option) ? $option : null),
            $options,
        ), static fn (mixed $value): bool => is_int($value) || is_string($value)));
    }
}
