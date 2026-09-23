<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Navigations;

use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Models\NavigationItem;
use LaraCeemes\Support\CeemesCache;

final class DeleteNavigationItem extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    public function execute(NavigationItem $item): void
    {
        $navigation = Navigation::query()->findOrFail($item->navigation_uuid);

        $this->transaction(function () use ($item, $navigation): void {
            $item->delete();
            $this->cache->forget("site:{$navigation->site_uuid}:navigation:{$navigation->handle}");
        });
    }
}
