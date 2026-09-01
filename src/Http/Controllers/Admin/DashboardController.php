<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use LaraCeemes\Models\CategoryGroup;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Media;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Models\Set;

final class DashboardController extends AdminController
{
    public function __invoke(): View
    {
        return $this->render('ceemes::admin.dashboard', [
            'counts' => [
                'Sets' => Set::query()->count(),
                'Contents' => Content::query()->count(),
                'Categories' => CategoryGroup::query()->count(),
                'Navigations' => Navigation::query()->count(),
                'Media' => Media::query()->count(),
            ],
            'recentEntries' => Content::query()
                ->with('set')
                ->latest('updated_at')
                ->limit(6)
                ->get(),
        ]);
    }
}
