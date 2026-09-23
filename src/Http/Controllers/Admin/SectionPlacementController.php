<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Section;
use LaraCeemes\Support\ContentCacheInvalidator;

final class SectionPlacementController extends AdminController
{
    public function store(Request $request, Content $content, ContentCacheInvalidator $cache): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'section_uuid' => [
                'required',
                'uuid',
                Rule::exists('ceemes_sections', 'uuid')->where('site_uuid', $content->site_uuid),
            ],
            'region' => ['nullable', 'alpha_dash:ascii', 'max:255'],
        ])->validate();
        $region = (string) ($validated['region'] ?? 'sections');
        $lastSortOrder = $content->placedSections()->wherePivot('region', $region)->max('ceemes_content_section.sort_order');
        $next = $lastSortOrder === null ? 0 : ((int) $lastSortOrder) + 1;

        if (! $content->placedSections()->wherePivot('region', $region)->whereKey($validated['section_uuid'])->exists()) {
            $content->placedSections()->attach($validated['section_uuid'], [
                'uuid' => (string) Str::uuid(),
                'region' => $region,
                'sort_order' => $next,
                'is_enabled' => true,
            ]);
            $cache->content($content);
        }

        return $this->success('ceemes.admin.contents.edit', 'Shared Section attached.', $content);
    }

    public function destroy(Content $content, Section $section, ContentCacheInvalidator $cache): RedirectResponse
    {
        $content->placedSections()->detach($section->uuid);
        $cache->content($content);

        return $this->success('ceemes.admin.contents.edit', 'Section detached. The shared Section remains in the library.', $content);
    }
}
