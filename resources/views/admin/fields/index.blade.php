@extends('ceemes::admin.layout', ['title' => 'Fields · '.$blueprint->name])

@section('content')
    <div class="ceemes-page-header">
        <div><a class="ceemes-back" href="{{ route('ceemes.admin.blueprints.index', $blueprint->collection) }}">&larr; Blueprints</a><span class="ceemes-eyebrow">{{ $blueprint->collection->name }} / {{ $blueprint->name }}</span><h1>Blueprint Fields</h1><p>Susun input yang akan muncul saat editor membuat Entry.</p></div>
        <button class="ceemes-button" type="button" data-dialog-open="create-field">+ Tambah Field</button>
    </div>

    <div class="ceemes-setup-steps"><div class="is-complete"><span>1</span><div><strong>Collection</strong><small>{{ $blueprint->collection->name }}</small></div></div><div class="is-complete"><span>2</span><div><strong>Blueprint</strong><small>{{ $blueprint->name }}</small></div></div><div class="is-current"><span>3</span><div><strong>Fields</strong><small>{{ $fields->count() }} input tersedia</small></div></div><div><span>4</span><div><strong>Entry</strong><small>Isi konten</small></div></div></div>

    <div class="ceemes-content-model-help"><article><span>Fixed</span><div><h3>Field biasa</h3><p>Cocok untuk data yang selalu ada dan posisinya pasti, seperti headline, excerpt, tanggal, atau featured image.</p></div></article><article><span>Builder</span><div><h3>Sections field</h3><p>Membuat area fleksibel tempat editor menyusun Section Type reusable seperti Hero, FAQ, Gallery, dan CTA.</p></div><button class="ceemes-button ceemes-button-secondary ceemes-button-small" type="button" data-dialog-open="create-field" data-set-field-type="sections">Tambah Sections area</button></article></div>

    <section class="ceemes-panel" data-resource-list>
        <div class="ceemes-toolbar"><label class="ceemes-search-box"><span aria-hidden="true">Search</span><input type="search" placeholder="Cari field..." data-table-search></label><span class="ceemes-result-count"><strong data-visible-count>{{ $fields->count() }}</strong> field</span></div>
        <div class="ceemes-table-wrap"><table class="ceemes-table"><thead><tr><th>Field</th><th>Type</th><th>Handle</th><th>Width</th><th>Required</th><th class="ceemes-actions-column">Aksi</th></tr></thead><tbody>
        @forelse($fields as $field)
            <tr data-table-row data-search="{{ strtolower($field->label.' '.$field->handle.' '.$field->type) }}"><td class="ceemes-cell-primary">{{ $field->label }}</td><td><span class="ceemes-badge">{{ $field->type }}</span></td><td><code>{{ $field->handle }}</code></td><td>{{ $field->width }}%</td><td>{{ ($field->config['required'] ?? false) ? 'Yes' : 'No' }}</td><td class="ceemes-row-actions"><button class="ceemes-button ceemes-button-secondary ceemes-button-small" type="button" data-dialog-open="edit-field-{{ $field->uuid }}">Edit</button><form method="POST" action="{{ route('ceemes.admin.fields.destroy', $field) }}" data-confirm="Hapus Field {{ $field->label }}?">@csrf @method('DELETE')<button class="ceemes-icon-button is-danger" type="submit">Hapus</button></form></td></tr>
        @empty<tr><td colspan="6"><div class="ceemes-empty"><span>+</span><h3>Belum ada Field</h3><p>Mulai dengan field seperti Headline, Content, atau Featured Image.</p><button class="ceemes-button" type="button" data-dialog-open="create-field">Tambah Field pertama</button></div></td></tr>@endforelse
        </tbody></table></div><div class="ceemes-no-results" data-no-results hidden>Tidak ada field yang cocok.</div>
    </section>

    <div class="ceemes-next-step"><div><strong>Sudah selesai menyusun field?</strong><p>Blueprint siap digunakan untuk membuat Entry di {{ $blueprint->collection->name }}.</p></div><a class="ceemes-button" href="{{ route('ceemes.admin.entries.create', $blueprint->collection) }}">Buat Entry &rarr;</a></div>

    <dialog class="ceemes-dialog" id="create-field" @if($errors->any()) data-open-on-error @endif><form method="POST" action="{{ route('ceemes.admin.fields.store', $blueprint) }}">@csrf
        <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">{{ $blueprint->name }}</span><h2>Tambah Field</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div>
        <div class="ceemes-dialog-body">@include('ceemes::admin.fields.form', ['field' => null, 'formPrefix' => 'create'])</div><div class="ceemes-dialog-footer"><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button class="ceemes-button" type="submit">Tambah Field</button></div>
    </form></dialog>

    @foreach($fields as $field)
        <dialog class="ceemes-dialog" id="edit-field-{{ $field->uuid }}"><form method="POST" action="{{ route('ceemes.admin.fields.update', $field) }}">@csrf @method('PUT')
            <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Edit Field</span><h2>{{ $field->label }}</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div><div class="ceemes-dialog-body">@include('ceemes::admin.fields.form', ['field' => $field, 'formPrefix' => 'edit-'.$field->uuid])</div><div class="ceemes-dialog-footer"><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button class="ceemes-button" type="submit">Simpan</button></div>
        </form></dialog>
    @endforeach
@endsection
