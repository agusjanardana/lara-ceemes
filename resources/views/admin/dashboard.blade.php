@extends('ceemes::admin.layout', ['title' => 'Dashboard'])

@section('content')
    <div class="ceemes-page-header"><div><span class="ceemes-eyebrow">Overview</span><h1>Selamat datang kembali.</h1><p>Ringkasan konten dan aktivitas terbaru Lara Ceemes.</p></div><a class="ceemes-button" href="{{ route('ceemes.admin.collections.index') }}">Kelola konten →</a></div>
    <div class="ceemes-stats-grid">
        @foreach($counts as $label => $count)<div class="ceemes-stat-card"><span class="ceemes-stat-icon">{{ ['Collections' => '▤', 'Entries' => '✦', 'Taxonomies' => '◇', 'Navigations' => '⑂', 'Media' => '▧'][$label] ?? '•' }}</span><div><small>{{ $label }}</small><strong>{{ number_format($count) }}</strong></div></div>@endforeach
    </div>
    <div class="ceemes-dashboard-grid">
        <section class="ceemes-panel"><div class="ceemes-panel-header"><div><h2>Entry terbaru</h2><p>Konten yang terakhir diperbarui</p></div></div><div class="ceemes-table-wrap"><table class="ceemes-table"><thead><tr><th>Title</th><th>Collection</th><th>Status</th><th>Updated</th></tr></thead><tbody>
            @forelse($recentEntries as $entry)<tr><td class="ceemes-cell-primary"><a href="{{ route('ceemes.admin.entries.edit', $entry) }}">{{ $entry->title }}</a><small>/{{ $entry->slug }}</small></td><td>{{ $entry->collection->name }}</td><td><span class="ceemes-badge {{ $entry->status->value === 'published' ? 'is-success' : 'is-warning' }}">{{ ucfirst($entry->status->value) }}</span></td><td>{{ $entry->updated_at?->diffForHumans() }}</td></tr>@empty<tr><td colspan="4"><div class="ceemes-empty"><h3>Belum ada Entry</h3><p>Buka Collection untuk membuat konten pertama.</p></div></td></tr>@endforelse
        </tbody></table></div></section>
        <aside class="ceemes-quick-card"><span class="ceemes-eyebrow">Quick start</span><h2>Bangun halaman pertama</h2><p>Mulai dari Collection Pages, siapkan Blueprint, lalu buat Entry yang dapat dikonsumsi frontend.</p><a href="{{ route('ceemes.admin.collections.index') }}">Buka Collections →</a></aside>
    </div>
@endsection
