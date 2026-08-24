<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Events\SectionCreated;
use LaraCeemes\Models\Section;
use LaraCeemes\Support\ContentCacheInvalidator;

final class DuplicateSection extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    public function execute(Section $section, ?string $key = null): Section
    {
        Validator::make(['key' => $key], [
            'key' => [
                'nullable',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('ceemes_sections', 'key')
                    ->where('entry_uuid', $section->entry_uuid)
                    ->where('field_handle', $section->field_handle),
            ],
        ])->validate();

        return $this->transaction(function () use ($section, $key): Section {
            $section->entry->sectionItems()
                ->where('field_handle', $section->field_handle)
                ->where('sort_order', '>', $section->sort_order)
                ->increment('sort_order');

            $duplicate = $section->replicate(['uuid', 'key', 'created_at', 'updated_at']);
            $duplicate->setAttribute('uuid', null);
            $duplicate->key = $key;
            $duplicate->sort_order = $section->sort_order + 1;
            $duplicate->save();

            $this->cache->entry($section->entry);
            event(new SectionCreated($duplicate));

            return $duplicate;
        });
    }
}
