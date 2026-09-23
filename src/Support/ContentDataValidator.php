<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Fields\FieldRegistry;
use LaraCeemes\Models\Set;
use LaraCeemes\Models\SetField;

final class ContentDataValidator
{
    public function __construct(
        private readonly FieldRegistry $fields,
        private readonly FieldVisibility $visibility,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function validate(Set $set, array $data): array
    {
        $setFields = $set->fields()->get();
        $knownHandles = $setFields->pluck('handle')->all();
        $unknownHandles = array_diff(array_keys($data), $knownHandles);

        if ($unknownHandles !== []) {
            throw ValidationException::withMessages([
                'data' => 'Unknown fields: '.implode(', ', $unknownHandles).'.',
            ]);
        }

        $rules = [];
        $messages = [];
        $attributes = [];

        foreach ($setFields as $field) {
            $config = $this->config($field);
            if (! $this->visibility->allows($config, $data)) {
                continue;
            }

            $rules[$field->handle] = $this->fields
                ->get($field->type)
                ->rules($config);
            $attributes[$field->handle] = $field->label;
            $messages = [...$messages, ...$this->messages($field->handle, $field->label, $config)];
        }

        $validated = Validator::make($data, $rules, $messages, $attributes)->validate();
        $serialized = [];

        foreach ($setFields as $field) {
            $config = $this->config($field);

            if (! $this->visibility->allows($config, $data)) {
                continue;
            }

            if (array_key_exists($field->handle, $validated)) {
                $serialized[$field->handle] = $this->fields
                    ->get($field->type)
                    ->serialize($validated[$field->handle], $config);

                continue;
            }

            if (array_key_exists('default', $config)) {
                $serialized[$field->handle] = $this->fields
                    ->get($field->type)
                    ->serialize($config['default'], $config);
            }
        }

        return $serialized;
    }

    /**
     * @param  array<string, mixed>|null  $seo
     * @return array<string, mixed>|null
     */
    public function validateSeo(?array $seo): ?array
    {
        if ($seo === null) {
            return null;
        }

        return Validator::make($seo, [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'url'],
            'og_image' => ['nullable', 'uuid'],
            'robots_index' => ['nullable', 'boolean'],
            'robots_follow' => ['nullable', 'boolean'],
        ])->validate();
    }

    /** @return array<string, mixed> */
    private function config(SetField $field): array
    {
        return is_array($field->config) ? $field->config : [];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, string>
     */
    private function messages(string $handle, string $label, array $config): array
    {
        $custom = trim((string) ($config['validation_message'] ?? ''));
        if ($custom !== '') {
            return ["{$handle}.*" => $custom];
        }

        return [
            "{$handle}.required" => "{$label} wajib diisi.",
            "{$handle}.min" => "{$label} belum memenuhi batas minimum yang ditentukan.",
            "{$handle}.max" => "{$label} melebihi batas maksimum yang ditentukan.",
            "{$handle}.email" => "{$label} harus berupa alamat email yang valid.",
            "{$handle}.url" => "{$label} harus berupa URL yang valid.",
            "{$handle}.integer" => "{$label} harus berupa bilangan bulat.",
            "{$handle}.numeric" => "{$label} harus berupa angka.",
            "{$handle}.date" => "{$label} harus berupa tanggal yang valid.",
            "{$handle}.date_format" => "{$label} harus berupa tanggal yang valid.",
            "{$handle}.after_or_equal" => "{$label} lebih awal dari tanggal minimum yang diizinkan.",
            "{$handle}.before_or_equal" => "{$label} melewati tanggal maksimum yang diizinkan.",
            "{$handle}.in" => "Pilihan {$label} tidak valid.",
        ];
    }
}
