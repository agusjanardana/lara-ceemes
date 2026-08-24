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
use LaraCeemes\Models\Entry;
use LaraCeemes\Models\Section;
use LaraCeemes\Models\SectionType;

final class SectionController extends AdminController
{
    public function store(Request $request, Entry $entry, CreateSection $action): RedirectResponse
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
        $action->execute($entry, $sectionType, $data);

        return $this->success('ceemes.admin.entries.edit', 'Section added.', $entry);
    }

    public function update(Request $request, Section $section, UpdateSection $action): RedirectResponse
    {
        $action->execute($section, [
            'key' => $request->input('key') ?: null,
            'data' => $this->normalizeFieldFormData(
                $this->jsonObject($request->input('data'), 'data'),
                $section->sectionType->fields,
            ),
        ]);

        return $this->success('ceemes.admin.entries.edit', 'Section updated.', $section->entry);
    }

    public function duplicate(Section $section, DuplicateSection $action): RedirectResponse
    {
        $action->execute($section);

        return $this->success('ceemes.admin.entries.edit', 'Section duplicated.', $section->entry);
    }

    public function toggle(Section $section, SetSectionEnabled $action): RedirectResponse
    {
        $action->execute($section, ! $section->is_enabled);

        return $this->success('ceemes.admin.entries.edit', 'Section status updated.', $section->entry);
    }

    public function destroy(Section $section, DeleteSection $action): RedirectResponse
    {
        $entry = $section->entry;
        $action->execute($section);

        return $this->success('ceemes.admin.entries.edit', 'Section deleted.', $entry);
    }
}
