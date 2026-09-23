<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Fields\FieldRegistry;
use LaraCeemes\Models\SectionField;

final class UpdateSectionField extends Action
{
    public function __construct(private readonly FieldRegistry $fields) {}

    /** @param array<string, mixed> $data */
    public function execute(SectionField $field, array $data): SectionField
    {
        $validated = Validator::make($data, [
            'handle' => [
                'sometimes',
                'required',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('ceemes_section_fields', 'handle')
                    ->where('section_type_uuid', $field->section_type_uuid)
                    ->ignore($field->uuid, 'uuid'),
            ],
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', Rule::in(array_keys($this->fields->all()))],
            'config' => ['sometimes', 'array'],
            'width' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();

        return $this->transaction(function () use ($field, $validated): SectionField {
            $previousHandle = $field->handle;
            $field->update($validated);

            if ($field->handle !== $previousHandle) {
                $field->sectionType->fields()
                    ->where('uuid', '!=', $field->uuid)
                    ->get()
                    ->each(function (SectionField $dependent) use ($previousHandle, $field): void {
                        $config = is_array($dependent->config) ? $dependent->config : [];
                        $visibility = is_array($config['visibility'] ?? null) ? $config['visibility'] : [];
                        if (($visibility['field'] ?? null) !== $previousHandle) {
                            return;
                        }

                        $visibility['field'] = $field->handle;
                        $config['visibility'] = $visibility;
                        $dependent->update(['config' => $config]);
                    });
            }

            return $field->refresh();
        });
    }
}
