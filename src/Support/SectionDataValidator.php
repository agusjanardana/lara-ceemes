<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Fields\FieldRegistry;
use LaraCeemes\Models\SectionField;
use LaraCeemes\Models\SectionType;

final class SectionDataValidator
{
    public function __construct(private readonly FieldRegistry $fields) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function validate(SectionType $sectionType, array $data): array
    {
        $sectionFields = $sectionType->fields()->get();
        $knownHandles = $sectionFields->pluck('handle')->all();
        $unknownHandles = array_diff(array_keys($data), $knownHandles);

        if ($unknownHandles !== []) {
            throw ValidationException::withMessages([
                'data' => 'Unknown Section fields: '.implode(', ', $unknownHandles).'.',
            ]);
        }

        $rules = [];

        foreach ($sectionFields as $field) {
            $rules[$field->handle] = $this->fields->get($field->type)->rules($this->config($field));
        }

        $validated = Validator::make($data, $rules)->validate();
        $serialized = [];

        foreach ($sectionFields as $field) {
            $config = $this->config($field);

            if (array_key_exists($field->handle, $validated)) {
                $serialized[$field->handle] = $this->fields
                    ->get($field->type)
                    ->serialize($validated[$field->handle], $config);
            } elseif (array_key_exists('default', $config)) {
                $serialized[$field->handle] = $this->fields
                    ->get($field->type)
                    ->serialize($config['default'], $config);
            }
        }

        return $serialized;
    }

    /** @return array<string, mixed> */
    private function config(SectionField $field): array
    {
        return is_array($field->config) ? $field->config : [];
    }
}
