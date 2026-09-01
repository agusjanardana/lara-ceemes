<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Sections\CreateSection;
use LaraCeemes\Actions\Sections\DeleteSection;
use LaraCeemes\Actions\Sections\DuplicateSection;
use LaraCeemes\Actions\Sections\SetSectionEnabled;
use LaraCeemes\Actions\Sections\UpdateSection;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Section;
use LaraCeemes\Models\SectionType;

final class SectionController extends AdminController
{
    public function store(Request $request, Content $content, CreateSection $action): RedirectResponse
    {
        $sectionType = SectionType::query()
            ->whereKey($request->string('section_type_uuid')->toString())
            ->firstOrFail();
        $data = $request->all();
        $data['key'] = $request->input('key') ?: null;
        $raw = $request->input('data');

        if (! is_array($raw) && ! is_string($raw)) {
            $sectionData = $request->input('section_data', []);
            $raw = is_array($sectionData) && is_array($sectionData[$sectionType->uuid] ?? null)
                ? $sectionData[$sectionType->uuid]
                : [];
        }

        $data['data'] = $this->normalizeFieldFormData(
            $this->jsonObject($raw, 'data'),
            $sectionType->fields,
        );
        $action->execute($content, $sectionType, $data);

        return $this->success('ceemes.admin.contents.edit', 'Reusable Section created and attached.', $content);
    }

    public function update(Request $request, Section $section, UpdateSection $action): RedirectResponse
    {
        $data = [
            'data' => $this->normalizeFieldFormData(
                $this->jsonObject($request->input('data'), 'data'),
                $section->sectionType->fields,
            ),
        ];

        foreach (['name', 'handle'] as $attribute) {
            if ($request->filled($attribute)) {
                $data[$attribute] = $request->input($attribute);
            }
        }

        $action->execute($section, $data);
        $content = Content::query()->findOrFail((string) $request->input('content_uuid'));
        $content->placedSections()->updateExistingPivot($section->uuid, [
            'key' => $request->input('key') ?: null,
        ]);

        return $this->success('ceemes.admin.contents.edit', 'Shared Section updated everywhere it is used.', $content);
    }

    public function duplicate(Section $section, DuplicateSection $action): RedirectResponse
    {
        $content = Content::query()->findOrFail((string) request()->input('content_uuid'));
        $action->execute($section, content: $content);

        return $this->success('ceemes.admin.contents.edit', 'Independent Section copy created.', $content);
    }

    public function toggle(Section $section, SetSectionEnabled $action): RedirectResponse
    {
        $action->execute($section, ! $section->is_enabled);

        $content = $section->contents()->firstOrFail();

        return $this->success('ceemes.admin.contents.edit', 'Section status updated.', $content);
    }

    public function destroy(Section $section, DeleteSection $action): RedirectResponse
    {
        $action->execute($section);

        return $this->success('ceemes.admin.sections.index', 'Section deleted.');
    }
}
