@extends('ceemes::admin.layout', ['title' => $set->name])

@section('content')
    <div class="ceemes-page-header"><div><a class="ceemes-back" href="{{ route('ceemes.admin.sets.index') }}">← Sets</a><h1>{{ $set->name }}</h1><p>{{ $set->description ?: 'Kelola seluruh Content pada Set ini.' }}</p></div><div class="ceemes-header-actions"><a class="ceemes-button" href="{{ route('ceemes.admin.contents.create', $set) }}"><span>＋</span> Buat Content</a></div></div>
    <section class="ceemes-panel">
        <form class="ceemes-toolbar" method="GET">
            <label class="ceemes-search-box"><span>⌕</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Cari title atau slug…"></label>
            <select class="ceemes-filter" name="status" onchange="this.form.submit()"><option value="">Semua status</option><option value="published" @selected(request('status') === 'published')>Published</option><option value="draft" @selected(request('status') === 'draft')>Draft</option></select>
            <button class="ceemes-button ceemes-button-secondary" type="submit">Filter</button>
            @if(request()->hasAny(['q','status']))<a class="ceemes-button ceemes-button-ghost" href="{{ route('ceemes.admin.contents.index', $set) }}">Reset</a>@endif
        </form>
        <div class="ceemes-table-wrap"><table class="ceemes-table"><thead><tr><th>Title</th><th>Status</th><th>Public URL</th><th>Updated</th><th class="ceemes-actions-column">Aksi</th></tr></thead><tbody>
            @forelse($contents as $content)
                <tr><td class="ceemes-cell-primary"><a href="{{ route('ceemes.admin.contents.edit', $content) }}">{{ $content->title }}</a></td><td><span class="ceemes-badge {{ $content->status->value === 'published' ? 'is-success' : 'is-warning' }}">{{ ucfirst($content->status->value) }}</span></td><td class="ceemes-muted"><a href="{{ $content->publicUrl() }}" target="_blank">{{ $content->uri }}</a></td><td>{{ $content->updated_at?->diffForHumans() }}</td><td class="ceemes-row-actions"><a class="ceemes-icon-button" href="{{ route('ceemes.admin.contents.edit', $content) }}" title="Edit">✎</a><form method="POST" action="{{ route('ceemes.admin.contents.destroy', $content) }}" data-confirm="Hapus Content {{ $content->title }}?">@csrf @method('DELETE')<button class="ceemes-icon-button is-danger" type="submit">⌫</button></form></td></tr>
            @empty<tr><td colspan="5"><div class="ceemes-empty"><span>＋</span><h3>Belum ada Content</h3><p>Buat Content pertama untuk Set {{ $set->name }}, lalu susun reusable Sections.</p><a class="ceemes-button" href="{{ route('ceemes.admin.contents.create', $set) }}">Buat Content pertama</a></div></td></tr>@endforelse
        </tbody></table></div>
        @if($contents->hasPages())<div class="ceemes-pagination"><span>Menampilkan {{ $contents->firstItem() }}–{{ $contents->lastItem() }} dari {{ $contents->total() }}</span><div class="ceemes-pagination-links">@if($contents->onFirstPage())<span>←</span>@else<a href="{{ $contents->previousPageUrl() }}">←</a>@endif @if($contents->hasMorePages())<a href="{{ $contents->nextPageUrl() }}">→</a>@else<span>→</span>@endif</div></div>@endif
    </section>
@endsection
