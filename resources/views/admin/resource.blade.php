@extends('ceemes::admin.layout', ['title' => $title])

@section('content')
    @php
        $resourceList = collect($resources);
        $tableFields = collect($fields)->filter(fn ($field) => ($field['table'] ?? true) && ($field['type'] ?? 'text') !== 'textarea')->take(3);
        if ($tableFields->isEmpty()) $tableFields = collect($fields)->take(2);
        $filterField = collect($fields)->first(fn ($field) => ($field['type'] ?? '') === 'select' && in_array($field['name'], ['type', 'status'], true));
    @endphp

    <div class="ceemes-page-header">
        <div>@isset($backUrl)<a class="ceemes-back" href="{{ $backUrl }}">← Kembali</a>@endisset<h1>{{ $title }}</h1><p>Kelola {{ strtolower($title) }} dari satu tempat.</p></div>
        <button class="ceemes-button" type="button" data-dialog-open="create-resource"><span>＋</span> Tambah baru</button>
    </div>

    <section class="ceemes-panel" data-resource-list>
        <div class="ceemes-toolbar">
            <label class="ceemes-search-box"><span>⌕</span><input type="search" placeholder="Cari {{ strtolower($title) }}…" data-table-search></label>
            @if($filterField)<select class="ceemes-filter" data-table-filter="{{ $filterField['name'] }}"><option value="">Semua {{ strtolower($filterField['label']) }}</option>@foreach($filterField['options'] ?? [] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>@endif
            <span class="ceemes-result-count"><strong data-visible-count>{{ $resourceList->count() }}</strong> item</span>
        </div>
        <div class="ceemes-table-wrap"><table class="ceemes-table">
            <thead><tr>@foreach($tableFields as $field)<th>{{ $field['label'] }}</th>@endforeach<th class="ceemes-actions-column">Aksi</th></tr></thead>
            <tbody>
            @forelse($resourceList as $index => $resource)
                @php($searchText = strtolower(implode(' ', array_map(fn ($value) => is_scalar($value) ? (string) $value : '', $resource['values']))))
                <tr data-table-row data-search="{{ $searchText }}" @if($filterField) data-{{ $filterField['name'] }}="{{ $resource['values'][$filterField['name']] ?? '' }}" @endif>
                    @foreach($tableFields as $fieldIndex => $field)
                        @php($value = $resource['values'][$field['name']] ?? null)
                        <td><div class="ceemes-cell {{ $fieldIndex === 0 ? 'ceemes-cell-primary' : '' }}" @if($fieldIndex === 0 && isset($resource['depth'])) style="padding-left: {{ $resource['depth'] * 28 }}px" @endif>
                            @if($fieldIndex === 0 && isset($resource['depth']) && $resource['depth'] > 0)<span class="ceemes-tree-line">↳</span>@endif
                            @if(is_bool($value))<span class="ceemes-badge {{ $value ? 'is-success' : 'is-muted' }}">{{ $value ? 'Active' : 'Inactive' }}</span>@elseif($value === null || $value === '')<span class="ceemes-muted">—</span>@else{{ \Illuminate\Support\Str::limit((string) $value, 70) }}@endif
                        </div></td>
                    @endforeach
                    <td class="ceemes-row-actions">
                        @foreach($resource['links'] ?? [] as $link)<a class="ceemes-button ceemes-button-secondary ceemes-button-small" href="{{ $link['url'] }}">{{ $link['label'] }}</a>@endforeach
                        <button class="ceemes-icon-button" type="button" data-dialog-open="edit-resource-{{ $index }}" title="Edit">✎</button>
                        <form method="POST" action="{{ $resource['delete_url'] }}" data-confirm="Hapus item ini? Tindakan ini tidak dapat dibatalkan.">@csrf @method('DELETE')<button class="ceemes-icon-button is-danger" type="submit" title="Hapus">⌫</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $tableFields->count() + 1 }}"><div class="ceemes-empty"><span>＋</span><h3>Belum ada data</h3><p>Tambahkan item pertama untuk mulai mengelola konten.</p></div></td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="ceemes-no-results" data-no-results hidden>Tidak ada data yang cocok dengan pencarian.</div>
    </section>

    <dialog class="ceemes-dialog" id="create-resource" @if($errors->any()) data-open-on-error @endif>
        <form method="POST" action="{{ $storeUrl }}">@csrf
            <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Create</span><h2>Tambah {{ $title }}</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>×</button></div>
            <div class="ceemes-dialog-body">@include('ceemes::admin.resource-fields', ['values' => [], 'useOld' => true, 'formPrefix' => 'create'])</div>
            <div class="ceemes-dialog-footer"><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button type="submit" class="ceemes-button">Simpan</button></div>
        </form>
    </dialog>

    @foreach($resourceList as $index => $resource)
        <dialog class="ceemes-dialog" id="edit-resource-{{ $index }}"><form method="POST" action="{{ $resource['update_url'] }}">@csrf @method('PUT')
            <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Edit</span><h2>{{ $resource['values'][$fields[0]['name']] ?? $title }}</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>×</button></div>
            <div class="ceemes-dialog-body">@include('ceemes::admin.resource-fields', ['values' => $resource['values'], 'useOld' => false, 'formPrefix' => 'edit-'.$index])</div>
            <div class="ceemes-dialog-footer"><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button type="submit" class="ceemes-button">Simpan perubahan</button></div>
        </form></dialog>
    @endforeach
@endsection
