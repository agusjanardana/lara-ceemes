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
    public function __construct(private readonly FieldRegistry $fields) {}

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

        foreach ($setFields as $field) {
            $rules[$field->handle] = $this->fields
                ->get($field->type)
                ->rules($this->config($field));
        }

        $validated = Validator::make($data, $rules)->validate();
        $serialized = [];

        foreach ($setFields as $field) {
            $config = $this->config($field);

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
}
