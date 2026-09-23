<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Fields\FieldRegistry;
use LaraCeemes\Models\SectionField;
use LaraCeemes\Models\Set;
use LaraCeemes\Models\SetField;

final class FieldConfiguration
{
    public function __construct(private readonly FieldRegistry $registry) {}

    /**
     * @param  iterable<int, SetField|SectionField>  $availableFields
     * @return array<string, mixed>
     */
    public function fromRequest(Request $request, iterable $availableFields = []): array
    {
        $input = $request->input('config', []);

        if (is_string($input)) {
            return $this->jsonObject($input, 'config');
        }

        $advanced = $this->jsonObject($request->input('advanced_config'), 'advanced_config');
        $config = [...$advanced, ...$this->stringKeyedArray($input)];
        $type = $request->string('type')->toString();
        $optionsText = is_string($config['options_text'] ?? null) ? $config['options_text'] : '';
        unset($config['options_text']);

        $config['required'] = $request->boolean('config.required');
        $config['multiple'] = $request->boolean('config.multiple');
        $config['integer'] = $request->boolean('config.integer');

        $this->normalizeText($config, ['placeholder', 'instructions', 'validation_message']);
        $this->normalizeInteger($config, 'min_length');
        $this->normalizeInteger($config, 'max_length');
        $this->normalizeInteger($config, 'min_items');
        $this->normalizeInteger($config, 'max_items');
        $this->normalizeNumber($config, 'min');
        $this->normalizeNumber($config, 'max');
        $this->normalizeText($config, ['after_or_equal', 'before_or_equal']);

        $stringTypes = ['text', 'textarea', 'richtext', 'email', 'url'];
        $collectionTypes = ['media', 'content', 'category', 'group', 'seo'];
        if (! in_array($type, $stringTypes, true)) {
            unset($config['min_length'], $config['max_length']);
        }
        if ($type !== 'number') {
            unset($config['min'], $config['max'], $config['integer']);
        }
        if (! in_array($type, $collectionTypes, true)) {
            unset($config['min_items'], $config['max_items']);
        }
        if (! in_array($type, ['date', 'datetime'], true)) {
            unset($config['after_or_equal'], $config['before_or_equal']);
        }
        if (! in_array($type, ['media', 'content', 'category'], true)) {
            unset($config['multiple']);
        }

        if (isset($config['min_length'], $config['max_length']) && $config['max_length'] < $config['min_length']) {
            throw ValidationException::withMessages(['config.max_length' => 'Panjang maksimum tidak boleh lebih kecil dari panjang minimum.']);
        }

        if (isset($config['min_items'], $config['max_items']) && $config['max_items'] < $config['min_items']) {
            throw ValidationException::withMessages(['config.max_items' => 'Jumlah maksimum tidak boleh lebih kecil dari jumlah minimum.']);
        }

        if (isset($config['min'], $config['max']) && $config['max'] < $config['min']) {
            throw ValidationException::withMessages(['config.max' => 'Nilai maksimum tidak boleh lebih kecil dari nilai minimum.']);
        }

        $this->normalizeOptions($config, $optionsText);
        $this->normalizeVisibility($config, $request, $availableFields);

        if ($type === 'sections') {
            $config['allowed'] = is_array($config['allowed'] ?? null)
                ? array_values(array_filter($config['allowed'], 'is_string'))
                : [];
        } else {
            unset($config['allowed']);
        }

        if ($type === 'content') {
            $set = is_string($config['set'] ?? null) ? $config['set'] : '';
            if (! Set::query()->where('handle', $set)->exists()) {
                throw ValidationException::withMessages(['config.set' => 'Pilih Set sumber untuk Content field.']);
            }
        } else {
            unset($config['set']);
        }

        if ($type === 'repeater') {
            $config['fields'] = $this->repeaterFields($config['fields'] ?? []);
            $this->normalizeInteger($config, 'min_rows');
            $this->normalizeInteger($config, 'max_rows');
            if (isset($config['min_rows'], $config['max_rows']) && $config['max_rows'] < $config['min_rows']) {
                throw ValidationException::withMessages(['config.max_rows' => 'Maximum item tidak boleh lebih kecil dari minimum item.']);
            }
        } else {
            unset($config['fields'], $config['min_rows'], $config['max_rows']);
        }

        return $config;
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<int, string>  $keys
     */
    private function normalizeText(array &$config, array $keys): void
    {
        foreach ($keys as $key) {
            $value = trim((string) ($config[$key] ?? ''));
            if ($value === '') {
                unset($config[$key]);
            } else {
                $config[$key] = $value;
            }
        }
    }

    /** @param array<string, mixed> $config */
    private function normalizeInteger(array &$config, string $key): void
    {
        $value = filter_var($config[$key] ?? null, FILTER_VALIDATE_INT);
        if ($value === false || $value < 0) {
            unset($config[$key]);
        } else {
            $config[$key] = $value;
        }
    }

    /** @param array<string, mixed> $config */
    private function normalizeNumber(array &$config, string $key): void
    {
        $value = $config[$key] ?? null;
        if ($value === null || $value === '' || ! is_numeric($value)) {
            unset($config[$key]);
        } else {
            $config[$key] = (float) $value;
        }
    }

    /** @param array<string, mixed> $config */
    private function normalizeOptions(array &$config, string $optionsText): void
    {
        if (trim($optionsText) === '') {
            unset($config['options']);

            return;
        }

        $config['options'] = collect(preg_split('/\r\n|\r|\n/', $optionsText) ?: [])
            ->filter(fn (string $line): bool => trim($line) !== '')
            ->mapWithKeys(function (string $line): array {
                $parts = explode(':', $line, 2);
                $value = trim($parts[0]);

                return [$value => trim($parts[1] ?? $value)];
            })->all();
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  iterable<int, SetField|SectionField>  $availableFields
     */
    private function normalizeVisibility(array &$config, Request $request, iterable $availableFields): void
    {
        $visibility = is_array($config['visibility'] ?? null) ? $config['visibility'] : [];
        $visibility['enabled'] = $request->boolean('config.visibility.enabled');

        if (! $visibility['enabled']) {
            unset($config['visibility']);

            return;
        }

        $source = trim((string) ($visibility['field'] ?? ''));
        $operator = (string) ($visibility['operator'] ?? 'filled');
        $handles = collect($availableFields)->map(fn (SetField|SectionField $field): string => $field->handle)->all();

        if ($source === '' || ! in_array($source, $handles, true) || $source === $request->string('handle')->toString()) {
            throw ValidationException::withMessages(['config.visibility.field' => 'Pilih field lain sebagai sumber kondisi visibility.']);
        }

        $operators = ['filled', 'empty', 'equals', 'not_equals', 'contains', 'not_contains', 'truthy', 'falsy'];
        if (! in_array($operator, $operators, true)) {
            throw ValidationException::withMessages(['config.visibility.operator' => 'Operator visibility tidak didukung.']);
        }

        $visibility = ['enabled' => true, 'field' => $source, 'operator' => $operator];
        if (in_array($operator, ['equals', 'not_equals', 'contains', 'not_contains'], true)) {
            $visibility['value'] = (string) ($config['visibility']['value'] ?? '');
        }

        $config['visibility'] = $visibility;
    }

    /** @return array<int, array<string, mixed>> */
    private function repeaterFields(mixed $input): array
    {
        $allowed = array_values(array_diff(array_keys($this->registry->all()), ['repeater', 'sections', 'group', 'seo']));
        $fields = [];

        foreach (is_array($input) ? $input : [] as $index => $field) {
            if (! is_array($field)) {
                continue;
            }
            $label = trim((string) ($field['label'] ?? ''));
            $handle = trim((string) ($field['handle'] ?? '')) ?: Str::slug($label, '_');
            $type = (string) ($field['type'] ?? 'text');

            if ($label === '' || $handle === '' || ! preg_match('/^[A-Za-z0-9_-]+$/', $handle)) {
                throw ValidationException::withMessages(["config.fields.{$index}.label" => 'Setiap subfield Repeater harus memiliki label dan handle yang valid.']);
            }
            if (! in_array($type, $allowed, true)) {
                throw ValidationException::withMessages(["config.fields.{$index}.type" => 'Tipe subfield Repeater tidak didukung.']);
            }
            if (collect($fields)->contains(fn (array $item): bool => $item['handle'] === $handle)) {
                throw ValidationException::withMessages(["config.fields.{$index}.handle" => "Handle '{$handle}' dipakai lebih dari sekali."]);
            }

            $subConfig = ['required' => filter_var($field['required'] ?? false, FILTER_VALIDATE_BOOLEAN)];
            if (trim((string) ($field['placeholder'] ?? '')) !== '') {
                $subConfig['placeholder'] = trim((string) $field['placeholder']);
            }
            $fields[] = ['label' => $label, 'handle' => $handle, 'type' => $type, 'width' => (int) ($field['width'] ?? 100), 'config' => $subConfig];
        }

        if ($fields === []) {
            throw ValidationException::withMessages(['config.fields' => 'Tambahkan minimal satu subfield yang akan diulang.']);
        }

        return $fields;
    }

    /** @return array<string, mixed> */
    private function jsonObject(mixed $value, string $field): array
    {
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode(is_string($value) && $value !== '' ? $value : '{}', true);
        if (! is_array($decoded)) {
            throw ValidationException::withMessages([$field => 'Harus berisi object JSON yang valid.']);
        }

        return $decoded;
    }

    /** @return array<string, mixed> */
    private function stringKeyedArray(mixed $value): array
    {
        $result = [];

        foreach (is_array($value) ? $value : [] as $key => $item) {
            if (is_string($key)) {
                $result[$key] = $item;
            }
        }

        return $result;
    }
}
