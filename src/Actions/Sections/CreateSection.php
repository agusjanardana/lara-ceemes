<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Events\SectionCreated;
use LaraCeemes\Models\Content;
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
    public function execute(Content $content, SectionType $sectionType, array $data): Section
    {
        $fieldHandle = is_string($data['field_handle'] ?? null) ? $data['field_handle'] : 'sections';
        $this->allowedSections->validate($content, $sectionType, $fieldHandle);

        $validated = Validator::make($data, [
            'name' => ['nullable', 'string', 'max:255'],
            'handle' => [
                'nullable', 'alpha_dash:ascii', 'max:255',
                Rule::unique('ceemes_sections', 'handle')->where('site_uuid', $content->site_uuid),
            ],
            'field_handle' => ['sometimes', 'alpha_dash:ascii', 'max:255'],
            'key' => ['nullable', 'alpha_dash:ascii', 'max:255'],
            'data' => ['sometimes', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_enabled' => ['sometimes', 'boolean'],
        ])->validate();

        $validated['name'] = trim((string) ($validated['name'] ?? ''))
            ?: (is_string($validated['key'] ?? null) ? Str::headline($validated['key']) : $sectionType->name);
        $validated['handle'] = trim((string) ($validated['handle'] ?? ''))
            ?: Str::slug($validated['name'].'-'.Str::lower(Str::random(6)));
        $validated['data'] = $this->sectionData->validate(
            $sectionType,
            is_array($validated['data'] ?? null) ? $validated['data'] : [],
        );
        $lastSortOrder = $content->placedSections()
            ->wherePivot('region', $fieldHandle)
            ->max('ceemes_content_section.sort_order');
        $validated['sort_order'] ??= $lastSortOrder === null ? 0 : ((int) $lastSortOrder) + 1;
        $validated['is_enabled'] ??= true;

        return $this->transaction(function () use ($content, $fieldHandle, $sectionType, $validated): Section {
            $section = Section::query()->create([
                'name' => $validated['name'],
                'handle' => $validated['handle'],
                'data' => $validated['data'],
                'section_type_uuid' => $sectionType->uuid,
                'site_uuid' => $content->site_uuid,
            ]);
            $content->placedSections()->attach($section->uuid, [
                'uuid' => (string) Str::uuid(),
                'region' => $fieldHandle,
                'key' => $validated['key'] ?? null,
                'sort_order' => $validated['sort_order'],
                'is_enabled' => $validated['is_enabled'],
            ]);

            $this->cache->content($content);
            event(new SectionCreated($section));

            return $section;
        });
    }
}
