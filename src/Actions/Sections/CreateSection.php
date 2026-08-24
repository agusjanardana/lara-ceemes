<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Events\SectionCreated;
use LaraCeemes\Models\Entry;
use LaraCeemes\Models\Section;
use LaraCeemes\Models\SectionType;
use LaraCeemes\Support\AllowedSectionValidator;
use LaraCeemes\Support\ContentCacheInvalidator;
use LaraCeemes\Support\SectionDataValidator;

final class CreateSection extends Action
{
    public function __construct(
        private readonly AllowedSectionValidator $allowedSections,
        private readonly SectionDataValidator $sectionData,
        private readonly ContentCacheInvalidator $cache,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(Entry $entry, SectionType $sectionType, array $data): Section
    {
        $fieldHandle = is_string($data['field_handle'] ?? null) ? $data['field_handle'] : 'sections';
        $this->allowedSections->validate($entry, $sectionType, $fieldHandle);

        $validated = Validator::make($data, [
            'field_handle' => ['sometimes', 'alpha_dash:ascii', 'max:255'],
            'key' => [
                'nullable',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('ceemes_sections', 'key')
                    ->where('entry_uuid', $entry->uuid)
                    ->where('field_handle', $fieldHandle),
            ],
            'data' => ['sometimes', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_enabled' => ['sometimes', 'boolean'],
        ])->validate();

        $validated['field_handle'] = $fieldHandle;
        $validated['data'] = $this->sectionData->validate(
            $sectionType,
            is_array($validated['data'] ?? null) ? $validated['data'] : [],
        );
        $validated['sort_order'] ??= ((int) $entry->sectionItems()
            ->where('field_handle', $fieldHandle)
            ->max('sort_order')) + 1;
        $validated['is_enabled'] ??= true;

        return $this->transaction(function () use ($entry, $sectionType, $validated): Section {
            $section = $entry->sectionItems()->create([
                ...$validated,
                'section_type_uuid' => $sectionType->uuid,
            ]);

            $this->cache->entry($entry);
            event(new SectionCreated($section));

            return $section;
        });
    }
}
