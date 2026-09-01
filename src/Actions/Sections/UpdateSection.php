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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'handle' => ['sometimes', 'required', 'alpha_dash:ascii', 'max:255', Rule::unique('ceemes_sections', 'handle')->ignore($section->uuid, 'uuid')],
            'data' => ['sometimes', 'array'],
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
            foreach ($section->contents()->get() as $content) {
                $this->cache->content($content);
            }
            event(new SectionUpdated($section));

            return $section;
        });
    }
}
