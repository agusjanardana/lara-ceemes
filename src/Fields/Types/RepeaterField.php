<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Fields\FieldRegistry;

final class RepeaterField extends AbstractArrayField
{
    public function __construct(private readonly Container $container) {}

    public function handle(): string
    {
        return 'repeater';
    }

    public function rules(array $config = []): array
    {
        $rules = parent::rules($config);

        if (isset($config['min_rows'])) {
            $rules[] = 'min:'.(int) $config['min_rows'];
        }

        if (isset($config['max_rows'])) {
            $rules[] = 'max:'.(int) $config['max_rows'];
        }

        $rules[] = function (string $attribute, mixed $value, Closure $fail) use ($config): void {
            if (! is_array($value)) {
                return;
            }

            try {
                foreach ($value as $index => $row) {
                    if (! is_array($row)) {
                        $fail('Item #'.($index + 1).' harus berupa data field.');

                        continue;
                    }

                    Validator::make($row, $this->rowRules($config), [], $this->rowLabels($config))->validate();
                }
            } catch (ValidationException $exception) {
                $fail(collect($exception->errors())->flatten()->first() ?? 'Isi Repeater tidak valid.');
            }
        };

        return $rules;
    }

    public function normalize(mixed $value, array $config = []): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        return array_values(array_map(function (array $row) use ($config): array {
            $normalized = [];

            foreach ($this->schemas($config) as $schema) {
                $handle = (string) $schema['handle'];
                if (! array_key_exists($handle, $row)) {
                    continue;
                }

                $normalized[$handle] = $this->fields()->get((string) $schema['type'])->serialize(
                    $row[$handle],
                    is_array($schema['config'] ?? null) ? $schema['config'] : [],
                );
            }

            return $normalized;
        }, $value));
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, array<int, mixed>>
     */
    private function rowRules(array $config): array
    {
        $rules = [];
        foreach ($this->schemas($config) as $schema) {
            $rules[(string) $schema['handle']] = $this->fields()->get((string) $schema['type'])->rules(
                is_array($schema['config'] ?? null) ? $schema['config'] : [],
            );
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, string>
     */
    private function rowLabels(array $config): array
    {
        return collect($this->schemas($config))->mapWithKeys(
            fn (array $schema): array => [(string) $schema['handle'] => (string) $schema['label']],
        )->all();
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, array<string, mixed>>
     */
    private function schemas(array $config): array
    {
        return array_values(array_filter($config['fields'] ?? [], fn (mixed $field): bool => is_array($field)
            && is_string($field['handle'] ?? null)
            && is_string($field['type'] ?? null)
            && $this->fields()->has($field['type'])));
    }

    private function fields(): FieldRegistry
    {
        return $this->container->make(FieldRegistry::class);
    }
}
