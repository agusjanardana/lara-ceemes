<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Fields\FieldRegistry;
use LaraCeemes\Models\SectionField;
use LaraCeemes\Models\SectionType;

final class CreateSectionField extends Action
{
    public function __construct(private readonly FieldRegistry $fields) {}

    /** @param array<string, mixed> $data */
    public function execute(SectionType $sectionType, array $data): SectionField
    {
        $data['handle'] ??= Str::slug((string) ($data['label'] ?? ''), '_');

        $validated = Validator::make($data, [
            'handle' => [
                'required',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('ceemes_section_fields', 'handle')->where('section_type_uuid', $sectionType->uuid),
            ],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys($this->fields->all()))],
            'config' => ['sometimes', 'array'],
            'width' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();

        return $this->transaction(fn (): SectionField => $sectionType->fields()->create($validated));
    }
}
