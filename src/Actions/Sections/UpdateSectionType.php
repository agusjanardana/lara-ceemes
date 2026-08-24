<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\SectionType;

final class UpdateSectionType extends Action
{
    /** @param array<string, mixed> $data */
    public function execute(SectionType $sectionType, array $data): SectionType
    {
        $validated = Validator::make($data, [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'handle' => [
                'sometimes',
                'required',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('ceemes_section_types', 'handle')->ignore($sectionType->uuid, 'uuid'),
            ],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
        ])->validate();

        return $this->transaction(function () use ($sectionType, $validated): SectionType {
            $sectionType->update($validated);

            return $sectionType->refresh();
        });
    }
}
