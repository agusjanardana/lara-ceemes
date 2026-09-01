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

final class SectionFieldController extends AdminController
{
    public function __construct(private readonly FieldRegistry $registry) {}

    public function index(SectionType $sectionType): View
    {
        return $this->render('ceemes::admin.resource', [
            'title' => "Section Fields: {$sectionType->name}",
            'backUrl' => route('ceemes.admin.section-types.index'),
            'storeUrl' => route('ceemes.admin.section-fields.store', $sectionType),
            'fields' => $this->fields(),
            'resources' => $sectionType->fields()->get()->map(fn (SectionField $field): array => [
                'values' => [
                    ...$field->only(['label', 'handle', 'type', 'width', 'sort_order']),
                    'config' => json_encode($field->config ?? [], JSON_PRETTY_PRINT),
                ],
                'update_url' => route('ceemes.admin.section-fields.update', $field),
                'delete_url' => route('ceemes.admin.section-fields.destroy', $field),
            ]),
        ]);
    }

    public function store(Request $request, SectionType $sectionType, CreateSectionField $action): RedirectResponse
    {
        $data = $request->all();
        $data['config'] = $this->jsonObject($request->input('config'), 'config');
        $action->execute($sectionType, $data);

        return $this->success('ceemes.admin.section-fields.index', 'Section Field created.', $sectionType);
    }

    public function update(Request $request, SectionField $sectionField, UpdateSectionField $action): RedirectResponse
    {
        $data = $request->all();
        $data['config'] = $this->jsonObject($request->input('config'), 'config');
        $action->execute($sectionField, $data);

        return $this->success('ceemes.admin.section-fields.index', 'Section Field updated.', $sectionField->sectionType);
    }

    public function destroy(SectionField $sectionField, DeleteSectionField $action): RedirectResponse
    {
        $sectionType = $sectionField->sectionType;
        $action->execute($sectionField);

        return $this->success('ceemes.admin.section-fields.index', 'Section Field deleted.', $sectionType);
    }

    /** @return array<int, array<string, mixed>> */
    private function fields(): array
    {
        $handles = array_keys($this->registry->all());
        $descriptions = [
            'text' => 'Teks pendek satu baris', 'textarea' => 'Teks panjang beberapa baris', 'richtext' => 'Konten teks dengan editor',
            'number' => 'Nilai angka', 'boolean' => 'Pilihan aktif atau tidak', 'select' => 'Pilihan dari daftar opsi',
            'date' => 'Tanggal', 'datetime' => 'Tanggal dan waktu', 'email' => 'Alamat email', 'url' => 'Alamat URL',
            'color' => 'Pemilih warna', 'media' => 'File dari Media Library', 'content' => 'Relasi ke Content dari Set',
            'category' => 'Relasi ke Category Group', 'group' => 'Kelompok data terstruktur', 'repeater' => 'Data berulang',
            'seo' => 'Data SEO terstruktur',
        ];

        return [
            ['name' => 'label', 'label' => 'Label'],
            ['name' => 'handle', 'label' => 'Handle'],
            ['name' => 'type', 'label' => 'Type', 'type' => 'field_type_picker', 'options' => array_combine($handles, array_map('ucfirst', $handles)), 'descriptions' => $descriptions],
            ['name' => 'config', 'label' => 'Config JSON', 'type' => 'textarea', 'default' => '{}'],
            ['name' => 'width', 'label' => 'Width', 'type' => 'number', 'default' => 100],
            ['name' => 'sort_order', 'label' => 'Sort order', 'type' => 'number', 'default' => 0],
        ];
    }
}
