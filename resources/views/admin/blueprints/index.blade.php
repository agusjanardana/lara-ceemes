@extends('ceemes::admin.layout', ['title' => 'Blueprints · '.$collection->name])

@section('content')
    <div class="ceemes-page-header">
        <div><a class="ceemes-back" href="{{ route('ceemes.admin.entries.index', $collection) }}">&larr; {{ $collection->name }}</a><span class="ceemes-eyebrow">Content model</span><h1>Blueprints</h1><p>Blueprint menentukan susunan field untuk setiap Entry di Collection {{ $collection->name }}.</p></div>
        <button class="ceemes-button" type="button" data-dialog-open="create-blueprint">+ Buat Blueprint</button>
    </div>

    <div class="ceemes-setup-steps">
        <div class="is-complete"><span>1</span><div><strong>Collection</strong><small>{{ $collection->name }}</small></div></div>
        <div class="{{ $blueprints->isNotEmpty() ? 'is-complete' : 'is-current' }}"><span>2</span><div><strong>Blueprint</strong><small>Tentukan model konten</small></div></div>
        <div class="{{ $blueprints->isNotEmpty() ? 'is-current' : '' }}"><span>3</span><div><strong>Fields</strong><small>Tambahkan input editor</small></div></div>
        <div><span>4</span><div><strong>Entry</strong><small>Mulai isi konten</small></div></div>
    </div>

    <section class="ceemes-panel" data-resource-list>
        <div class="ceemes-toolbar"><label class="ceemes-search-box"><span aria-hidden="true">Search</span><input type="search" placeholder="Cari Blueprint..." data-table-search></label><span class="ceemes-result-count"><strong data-visible-count>{{ $blueprints->count() }}</strong> Blueprint</span></div>
        <div class="ceemes-table-wrap"><table class="ceemes-table">
            <thead><tr><th>Blueprint</th><th>Handle</th><th>Fields</th><th>Entries</th><th class="ceemes-actions-column">Aksi</th></tr></thead>
            <tbody>
            @forelse($blueprints as $blueprint)
                <tr data-table-row data-search="{{ strtolower($blueprint->name.' '.$blueprint->handle) }}">
                    <td class="ceemes-cell-primary"><a href="{{ route('ceemes.admin.fields.index', $blueprint) }}">{{ $blueprint->name }}</a></td><td><code>{{ $blueprint->handle }}</code></td><td>{{ $blueprint->fields_count }}</td><td>{{ $blueprint->entries_count }}</td>
                    <td class="ceemes-row-actions"><a class="ceemes-button ceemes-button-secondary ceemes-button-small" href="{{ route('ceemes.admin.fields.index', $blueprint) }}">Kelola Fields</a><button class="ceemes-icon-button" type="button" data-dialog-open="edit-blueprint-{{ $blueprint->uuid }}" title="Edit">Edit</button><form method="POST" action="{{ route('ceemes.admin.blueprints.destroy', $blueprint) }}" data-confirm="Hapus Blueprint {{ $blueprint->name }}?">@csrf @method('DELETE')<button class="ceemes-icon-button is-danger" type="submit">Hapus</button></form></td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="ceemes-empty"><span>BP</span><h3>Collection ini belum memiliki Blueprint</h3><p>Contoh untuk Collection Pages: buat Blueprint bernama Standard Page.</p><button class="ceemes-button" type="button" data-dialog-open="create-blueprint">Buat Blueprint pertama</button></div></td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="ceemes-no-results" data-no-results hidden>Tidak ada Blueprint yang cocok.</div>
    </section>

    <dialog class="ceemes-dialog" id="create-blueprint" @if($errors->any()) data-open-on-error @endif><form method="POST" action="{{ route('ceemes.admin.blueprints.store', $collection) }}">@csrf
        <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Content model</span><h2>Buat Blueprint</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div>
        <div class="ceemes-dialog-body"><div class="ceemes-form-grid"><div class="ceemes-field"><label for="blueprint-name">Nama <em>*</em></label><input id="blueprint-name" name="name" value="{{ old('name') }}" placeholder="Standard Page" required data-slug-source="#blueprint-handle"><small>Nama yang mudah dipahami editor.</small></div><div class="ceemes-field"><label for="blueprint-handle">Handle</label><input id="blueprint-handle" name="handle" value="{{ old('handle') }}" placeholder="standard-page" data-slug-target><small>Boleh dikosongkan; dibuat otomatis dari nama.</small></div></div></div>
        <div class="ceemes-dialog-footer"><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button class="ceemes-button" type="submit">Buat &amp; tambahkan Fields</button></div>
    </form></dialog>

    @foreach($blueprints as $blueprint)
        <dialog class="ceemes-dialog" id="edit-blueprint-{{ $blueprint->uuid }}"><form method="POST" action="{{ route('ceemes.admin.blueprints.update', $blueprint) }}">@csrf @method('PUT')
            <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Edit Blueprint</span><h2>{{ $blueprint->name }}</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div>
            <div class="ceemes-dialog-body"><div class="ceemes-form-grid"><div class="ceemes-field"><label>Nama</label><input name="name" value="{{ $blueprint->name }}" required></div><div class="ceemes-field"><label>Handle</label><input name="handle" value="{{ $blueprint->handle }}" required></div></div></div>
            <div class="ceemes-dialog-footer"><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button class="ceemes-button" type="submit">Simpan</button></div>
        </form></dialog>
    @endforeach
@endsection
