<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Sections\CreateSectionField;
use LaraCeemes\Actions\Sections\DeleteSectionField;
use LaraCeemes\Actions\Sections\UpdateSectionField;
use LaraCeemes\Fields\FieldRegistry;
use LaraCeemes\Models\SectionField;
use LaraCeemes\Models\SectionType;
use LaraCeemes\Models\Set;
use LaraCeemes\Support\FieldConfiguration;

final class SectionFieldController extends AdminController
{
    public function __construct(
        private readonly FieldRegistry $registry,
        private readonly FieldConfiguration $fieldConfiguration,
    ) {}

    public function index(SectionType $sectionType): View
    {
        $fields = $sectionType->fields()->get();

        return $this->render('ceemes::admin.section-fields.index', [
            'sectionType' => $sectionType,
            'fields' => $fields,
            'fieldTypes' => array_keys($this->registry->all()),
            'sets' => Set::query()->orderBy('name')->get(),
            'sectionTypes' => SectionType::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, SectionType $sectionType, CreateSectionField $action): RedirectResponse
    {
        $data = $request->all();
        $data['config'] = $this->fieldConfiguration->fromRequest($request, $sectionType->fields()->get());
        $action->execute($sectionType, $data);

        return $this->success('ceemes.admin.section-fields.index', 'Section Field created.', $sectionType);
    }

    public function update(Request $request, SectionField $sectionField, UpdateSectionField $action): RedirectResponse
    {
        $data = $request->all();
        $data['config'] = $this->fieldConfiguration->fromRequest(
            $request,
            $sectionField->sectionType->fields()->where('uuid', '!=', $sectionField->uuid)->get(),
        );
        $action->execute($sectionField, $data);

        return $this->success('ceemes.admin.section-fields.index', 'Section Field updated.', $sectionField->sectionType);
    }

    public function destroy(SectionField $sectionField, DeleteSectionField $action): RedirectResponse
    {
        $sectionType = $sectionField->sectionType;
        $action->execute($sectionField);

        return $this->success('ceemes.admin.section-fields.index', 'Section Field deleted.', $sectionType);
    }
}
