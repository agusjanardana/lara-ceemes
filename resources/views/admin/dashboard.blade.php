@extends('ceemes::admin.layout', ['title' => 'Dashboard'])

@section('content')
    <div class="ceemes-page-header"><div><span class="ceemes-eyebrow">Overview</span><h1>Selamat datang kembali.</h1><p>Ringkasan konten dan aktivitas terbaru Lara Ceemes.</p></div><a class="ceemes-button" href="{{ route('ceemes.admin.sets.index') }}">Kelola konten →</a></div>
    <div class="ceemes-stats-grid">
        @foreach($counts as $label => $count)<div class="ceemes-stat-card"><span class="ceemes-stat-icon">{{ ['Sets' => '▤', 'Contents' => '✦', 'Categories' => '◇', 'Navigations' => '⑂', 'Media' => '▧'][$label] ?? '•' }}</span><div><small>{{ $label }}</small><strong>{{ number_format($count) }}</strong></div></div>@endforeach
    </div>
    <div class="ceemes-dashboard-grid">
        <section class="ceemes-panel"><div class="ceemes-panel-header"><div><h2>Content terbaru</h2><p>Konten yang terakhir diperbarui</p></div></div><div class="ceemes-table-wrap"><table class="ceemes-table"><thead><tr><th>Title</th><th>Set</th><th>Status</th><th>Updated</th></tr></thead><tbody>
            @forelse($recentEntries as $content)<tr><td class="ceemes-cell-primary"><a href="{{ route('ceemes.admin.contents.edit', $content) }}">{{ $content->title }}</a><small>/{{ $content->slug }}</small></td><td>{{ $content->set->name }}</td><td><span class="ceemes-badge {{ $content->status->value === 'published' ? 'is-success' : 'is-warning' }}">{{ ucfirst($content->status->value) }}</span></td><td>{{ $content->updated_at?->diffForHumans() }}</td></tr>@empty<tr><td colspan="4"><div class="ceemes-empty"><h3>Belum ada Content</h3><p>Buka Set untuk membuat konten pertama.</p></div></td></tr>@endforelse
        </tbody></table></div></section>
        <aside class="ceemes-quick-card"><span class="ceemes-eyebrow">Quick start</span><h2>Bangun halaman pertama</h2><p>Mulai dari Set Pages, tambahkan Fixed Fields bila perlu, lalu susun reusable Sections pada Content.</p><a href="{{ route('ceemes.admin.sets.index') }}">Buka Sets →</a></aside>
    </div>
@endsection
