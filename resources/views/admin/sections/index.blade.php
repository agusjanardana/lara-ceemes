@extends('ceemes::admin.layout', ['title' => 'Sections'])

@section('content')
    <div class="ceemes-page-header"><div><span class="ceemes-eyebrow">Reusable content</span><h1>Section Library</h1><p>Satu Section dapat dipasang pada beberapa Content. Perubahan Section shared berlaku di semua lokasi.</p></div><a class="ceemes-button ceemes-button-secondary" href="{{ route('ceemes.admin.section-types.index') }}">Kelola Section Types</a></div>
    <section class="ceemes-panel" data-resource-list>
        <div class="ceemes-toolbar"><label class="ceemes-search-box"><span>Search</span><input type="search" placeholder="Cari section..." data-table-search></label><span class="ceemes-result-count"><strong data-visible-count>{{ $sections->count() }}</strong> section</span></div>
        <div class="ceemes-table-wrap"><table class="ceemes-table"><thead><tr><th>Section</th><th>Type</th><th>Used in</th><th>Updated</th></tr></thead><tbody>
        @forelse($sections as $section)
            <tr data-table-row data-search="{{ strtolower(($section->name ?: $section->handle).' '.$section->sectionType->name) }}"><td class="ceemes-cell-primary">{{ $section->name ?: $section->handle }}<small>{{ $section->handle }}</small></td><td><span class="ceemes-badge">{{ $section->sectionType->name }}</span></td><td>@forelse($section->contents as $content)<a href="{{ route('ceemes.admin.contents.edit', $content) }}">{{ $content->set->name }} / {{ $content->title }}</a>@if(!$loop->last), @endif @empty<span class="ceemes-muted">Not used</span>@endforelse</td><td>{{ $section->updated_at?->diffForHumans() }}</td></tr>
        @empty<tr><td colspan="4"><div class="ceemes-empty"><span>+</span><h3>Belum ada reusable Section</h3><p>Buat Section pertama dari editor Content, lalu gunakan kembali dari Content lain.</p></div></td></tr>@endforelse
        </tbody></table></div><div class="ceemes-no-results" data-no-results hidden>Tidak ada Section yang cocok.</div>
    </section>
@endsection
