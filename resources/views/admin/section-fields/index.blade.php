@extends('ceemes::admin.layout', ['title' => 'Fields: '.$sectionType->name])

@section('content')
    <div class="ceemes-page-header">
        <div><a class="ceemes-back" href="{{ route('ceemes.admin.section-types.index') }}">&larr; Section Types</a><span class="ceemes-eyebrow">Section structure</span><h1>{{ $sectionType->name }} Fields</h1><p>Atur input, validation, dan conditional visibility untuk Section ini.</p></div>
        <button class="ceemes-button" type="button" data-dialog-open="create-section-field">+ Tambah Field</button>
    </div>

    <section class="ceemes-panel" data-resource-list>
        <div class="ceemes-toolbar"><label class="ceemes-search-box"><span>Search</span><input type="search" placeholder="Cari field..." data-table-search></label><span class="ceemes-result-count"><strong data-visible-count>{{ $fields->count() }}</strong> field</span></div>
        <div class="ceemes-table-wrap"><table class="ceemes-table"><thead><tr><th>Field</th><th>Type</th><th>Validation</th><th>Visibility</th><th class="ceemes-actions-column">Aksi</th></tr></thead><tbody>
            @forelse($fields as $field)
                @php($visibility = is_array($field->config['visibility'] ?? null) ? $field->config['visibility'] : [])
                <tr data-table-row data-search="{{ strtolower($field->label.' '.$field->handle.' '.$field->type) }}"><td><div class="ceemes-cell-primary">{{ $field->label }}<small>{{ $field->handle }}</small></div></td><td><span class="ceemes-badge">{{ $field->type }}</span></td><td>{{ ($field->config['required'] ?? false) ? 'Required' : 'Optional' }}</td><td>{{ ($visibility['enabled'] ?? false) ? 'Conditional' : 'Always visible' }}</td><td class="ceemes-row-actions"><button class="ceemes-icon-button" type="button" data-dialog-open="edit-section-field-{{ $field->uuid }}">Edit</button><form method="POST" action="{{ route('ceemes.admin.section-fields.destroy', $field) }}" data-confirm="Hapus field {{ $field->label }}?">@csrf @method('DELETE')<button class="ceemes-icon-button is-danger" type="submit">Hapus</button></form></td></tr>
            @empty
                <tr><td colspan="5"><div class="ceemes-empty"><span>+</span><h3>Belum ada Field</h3><p>Tambahkan field pertama untuk menyusun Section ini.</p></div></td></tr>
            @endforelse
        </tbody></table></div><div class="ceemes-no-results" data-no-results hidden>Tidak ada Field yang cocok.</div>
    </section>

    <dialog class="ceemes-dialog" id="create-section-field" @if($errors->any()) data-open-on-error @endif><form method="POST" action="{{ route('ceemes.admin.section-fields.store', $sectionType) }}">@csrf<div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">{{ $sectionType->name }}</span><h2>Tambah Field</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div><div class="ceemes-dialog-body">@include('ceemes::admin.fields.form', ['field' => null, 'formPrefix' => 'section-create-field'])</div><div class="ceemes-dialog-footer"><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button class="ceemes-button" type="submit">Tambah Field</button></div></form></dialog>

    @foreach($fields as $field)
        <dialog class="ceemes-dialog" id="edit-section-field-{{ $field->uuid }}"><form method="POST" action="{{ route('ceemes.admin.section-fields.update', $field) }}">@csrf @method('PUT')<div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">{{ $sectionType->name }}</span><h2>Edit {{ $field->label }}</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div><div class="ceemes-dialog-body">@include('ceemes::admin.fields.form', ['field' => $field, 'formPrefix' => 'section-edit-field-'.$field->uuid])</div><div class="ceemes-dialog-footer"><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button class="ceemes-button" type="submit">Simpan Field</button></div></form></dialog>
    @endforeach
@endsection
