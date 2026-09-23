<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Lara Ceemes' }} · Lara Ceemes</title>
    <link rel="stylesheet" href="{{ asset('vendor/ceemes/ceemes.css') }}">
    <script defer src="{{ asset('vendor/ceemes/ceemes.js') }}"></script>
</head>
<body class="ceemes-admin-body">
<div class="ceemes-app" data-ceemes-app>
    <header class="ceemes-topbar">
        <button class="ceemes-icon-button ceemes-menu-toggle" type="button" data-sidebar-toggle aria-label="Buka menu">☰</button>
        <a class="ceemes-logo" href="{{ route('ceemes.admin.dashboard') }}"><span>LC</span><strong>Lara Ceemes</strong></a>
        <div class="ceemes-topbar-spacer"></div>
        <label class="ceemes-global-search"><span>⌕</span><input type="search" placeholder="Cari di halaman ini…" data-global-search><kbd>⌘ K</kbd></label>
        @if(Route::has('ceemes.home'))<a class="ceemes-icon-button" href="{{ route('ceemes.home') }}" target="_blank" title="Lihat website">↗</a>@endif
        <div class="ceemes-user-menu"><span class="ceemes-avatar">{{ strtoupper(substr((string) (auth()->user()?->name ?? auth()->user()?->email ?? 'A'), 0, 1)) }}</span><span class="ceemes-user-name">{{ auth()->user()?->name ?? auth()->user()?->email ?? 'Admin' }}</span></div>
    </header>

    <aside class="ceemes-sidebar" data-sidebar>
        <div class="ceemes-sidebar-scroll">
            <a class="ceemes-nav-item {{ request()->routeIs('ceemes.admin.dashboard') ? 'is-active' : '' }}" href="{{ route('ceemes.admin.dashboard') }}"><span class="ceemes-nav-icon">⌂</span>Dashboard</a>
            <div class="ceemes-nav-label">Content</div>
            <div class="ceemes-nav-group" data-nav-group="sets">
                <div class="ceemes-nav-group-row"><a class="ceemes-nav-item {{ request()->routeIs('ceemes.admin.sets.*', 'ceemes.admin.set-fields.*', 'ceemes.admin.contents.*') ? 'is-active' : '' }}" href="{{ route('ceemes.admin.sets.index') }}"><span class="ceemes-nav-icon">▤</span>Sets</a><button type="button" data-nav-collapse aria-label="Tampilkan atau sembunyikan Sets">⌃</button></div>
                @if($sidebarSets->isNotEmpty())<div class="ceemes-nav-children" data-nav-children>@foreach($sidebarSets as $sidebarSet)<div class="ceemes-set-nav"><a class="{{ request()->routeIs('ceemes.admin.contents.*') && (request()->route('set')?->is($sidebarSet) || request()->route('content')?->set_uuid === $sidebarSet->uuid) ? 'is-active' : '' }}" href="{{ route('ceemes.admin.contents.index', $sidebarSet) }}">{{ $sidebarSet->name }}</a></div>@endforeach</div>@endif
            </div>
            <a class="ceemes-nav-item {{ request()->routeIs('ceemes.admin.sections.*', 'ceemes.admin.section-placements.*') ? 'is-active' : '' }}" href="{{ route('ceemes.admin.sections.index') }}"><span class="ceemes-nav-icon">◫</span>Sections</a>
            <a class="ceemes-nav-item {{ request()->routeIs('ceemes.admin.navigations.*', 'ceemes.admin.navigation-items.*') ? 'is-active' : '' }}" href="{{ route('ceemes.admin.navigations.index') }}"><span class="ceemes-nav-icon">⑂</span>Navigation</a>
            <div class="ceemes-nav-group" data-nav-group="categories">
                <div class="ceemes-nav-group-row"><a class="ceemes-nav-item {{ request()->routeIs('ceemes.admin.category-groups.*', 'ceemes.admin.categories.*') ? 'is-active' : '' }}" href="{{ route('ceemes.admin.category-groups.index') }}"><span class="ceemes-nav-icon">◇</span>Categories</a><button type="button" data-nav-collapse aria-label="Tampilkan atau sembunyikan Categories">⌃</button></div>
                @if($sidebarCategoryGroups->isNotEmpty())<div class="ceemes-nav-children" data-nav-children>@foreach($sidebarCategoryGroups as $sidebarGroup)<div class="ceemes-set-nav"><a class="{{ request()->routeIs('ceemes.admin.categories.*') && (request()->route('categoryGroup')?->is($sidebarGroup) || request()->route('category')?->category_group_uuid === $sidebarGroup->uuid) ? 'is-active' : '' }}" href="{{ route('ceemes.admin.categories.index', $sidebarGroup) }}">{{ $sidebarGroup->name }}</a></div>@endforeach</div>@endif
            </div>
            <a class="ceemes-nav-item {{ request()->routeIs('ceemes.admin.media.*', 'ceemes.admin.media-folders.*') ? 'is-active' : '' }}" href="{{ route('ceemes.admin.media.index') }}"><span class="ceemes-nav-icon">▧</span>Media</a>
            <div class="ceemes-nav-label">Fields</div>
            <a class="ceemes-nav-item {{ request()->routeIs('ceemes.admin.section-types.*', 'ceemes.admin.section-fields.*') ? 'is-active' : '' }}" href="{{ route('ceemes.admin.section-types.index') }}"><span class="ceemes-nav-icon">⊞</span>Section Types</a>
            <div class="ceemes-nav-label">System</div>
            <a class="ceemes-nav-item {{ request()->routeIs('ceemes.admin.settings.*') ? 'is-active' : '' }}" href="{{ route('ceemes.admin.settings.index') }}"><span class="ceemes-nav-icon">⚙</span>Settings</a>
        </div>
        @if(Route::has('ceemes.logout'))
            <form method="POST" action="{{ route('ceemes.logout') }}" class="ceemes-sidebar-footer">@csrf<button type="submit"><span>⇥</span> Logout</button></form>
        @endif
    </aside>
    <div class="ceemes-sidebar-backdrop" data-sidebar-close></div>

    <main class="ceemes-main">
        @if(session('success'))<div class="ceemes-toast ceemes-toast-success" role="status"><span>✓</span>{{ session('success') }}<button type="button" data-dismiss>×</button></div>@endif
        @if($errors->any())<div class="ceemes-alert ceemes-alert-danger"><strong>Periksa kembali input Anda.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</div>
</body>
</html>
