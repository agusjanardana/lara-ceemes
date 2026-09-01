<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use LaraCeemes\Actions\Action;
use LaraCeemes\Events\SectionCreated;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Section;
use LaraCeemes\Support\ContentCacheInvalidator;

final class DuplicateSection extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    public function execute(Section $section, ?string $key = null, ?Content $content = null): Section
    {
        Validator::make(['key' => $key], ['key' => ['nullable', 'alpha_dash:ascii', 'max:255']])->validate();

        return $this->transaction(function () use ($section, $key, $content): Section {
            $duplicate = $section->replicate(['uuid', 'created_at', 'updated_at']);
            $duplicate->setAttribute('uuid', null);
            $duplicate->name = ($section->name ?: $section->sectionType->name).' Copy';
            $duplicate->handle = Str::slug(($section->handle ?: $section->sectionType->handle).'-'.Str::lower(Str::random(6)));
            $duplicate->save();

            $targetContent = $content ?? $section->contents()->first();
            if ($targetContent !== null) {
                $placement = $section->contents()->whereKey($targetContent->uuid)->first()?->pivot;
                $targetContent->placedSections()->attach($duplicate->uuid, [
                    'uuid' => (string) Str::uuid(),
                    'region' => $placement?->region ?? 'sections',
                    'key' => $key,
                    'sort_order' => ((int) ($placement?->sort_order ?? 0)) + 1,
                    'is_enabled' => true,
                ]);
                $this->cache->content($targetContent);
            }
            event(new SectionCreated($duplicate));

            return $duplicate;
        });
    }
}
