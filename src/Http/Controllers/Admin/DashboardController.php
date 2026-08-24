<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use LaraCeemes\Models\Collection;
use LaraCeemes\Models\Entry;
use LaraCeemes\Models\Media;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Models\Taxonomy;

final class DashboardController extends AdminController
{
    public function __invoke(): View
    {
        return $this->render('ceemes::admin.dashboard', [
            'counts' => [
                'Collections' => Collection::query()->count(),
                'Entries' => Entry::query()->count(),
                'Taxonomies' => Taxonomy::query()->count(),
                'Navigations' => Navigation::query()->count(),
                'Media' => Media::query()->count(),
            ],
            'recentEntries' => Entry::query()
                ->with('collection')
                ->latest('updated_at')
                ->limit(6)
                ->get(),
        ]);
    }
}
