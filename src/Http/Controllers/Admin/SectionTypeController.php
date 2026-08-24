<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Sections\CreateSectionType;
use LaraCeemes\Actions\Sections\DeleteSectionType;
use LaraCeemes\Actions\Sections\UpdateSectionType;
use LaraCeemes\Models\SectionType;

final class SectionTypeController extends AdminController
{
    public function index(): View
    {
        return $this->render('ceemes::admin.resource', [
            'title' => 'Section Types',
            'storeUrl' => route('ceemes.admin.section-types.store'),
            'fields' => $this->fields(),
            'resources' => SectionType::query()->orderBy('name')->get()->map(fn (SectionType $type): array => [
                'values' => $type->only(['name', 'handle', 'description', 'icon']),
                'update_url' => route('ceemes.admin.section-types.update', $type),
                'delete_url' => route('ceemes.admin.section-types.destroy', $type),
                'links' => [['label' => 'Fields', 'url' => route('ceemes.admin.section-fields.index', $type)]],
            ]),
        ]);
    }

    public function store(Request $request, CreateSectionType $action): RedirectResponse
    {
        $action->execute($request->all());

        return $this->success('ceemes.admin.section-types.index', 'Section Type created.');
    }

    public function update(Request $request, SectionType $sectionType, UpdateSectionType $action): RedirectResponse
    {
        $action->execute($sectionType, $request->all());

        return $this->success('ceemes.admin.section-types.index', 'Section Type updated.');
    }

    public function destroy(SectionType $sectionType, DeleteSectionType $action): RedirectResponse
    {
        $action->execute($sectionType);

        return $this->success('ceemes.admin.section-types.index', 'Section Type deleted.');
    }

    /** @return array<int, array<string, string>> */
    private function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name'],
            ['name' => 'handle', 'label' => 'Handle'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ['name' => 'icon', 'label' => 'Icon'],
        ];
    }
}
