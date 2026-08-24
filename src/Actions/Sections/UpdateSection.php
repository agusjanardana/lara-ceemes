<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Events\SectionUpdated;
use LaraCeemes\Models\Section;
use LaraCeemes\Support\ContentCacheInvalidator;
use LaraCeemes\Support\SectionDataValidator;

final class UpdateSection extends Action
{
    public function __construct(
        private readonly SectionDataValidator $sectionData,
        private readonly ContentCacheInvalidator $cache,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(Section $section, array $data): Section
    {
        $validated = Validator::make($data, [
            'key' => [
                'nullable',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('ceemes_sections', 'key')
                    ->where('entry_uuid', $section->entry_uuid)
                    ->where('field_handle', $section->field_handle)
                    ->ignore($section->uuid, 'uuid'),
            ],
            'data' => ['sometimes', 'array'],
            'is_enabled' => ['sometimes', 'boolean'],
        ])->validate();

        if (array_key_exists('data', $validated)) {
            $validated['data'] = $this->sectionData->validate(
                $section->sectionType,
                is_array($validated['data']) ? $validated['data'] : [],
            );
        }

        return $this->transaction(function () use ($section, $validated): Section {
            $section->update($validated);
            $section->refresh();
            $this->cache->entry($section->entry);
            event(new SectionUpdated($section));

            return $section;
        });
    }
}
