<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Navigations;

use LaraCeemes\Actions\Action;
use LaraCeemes\Models\NavigationItem;
use LaraCeemes\Support\CeemesCache;

final class DeleteNavigationItem extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    public function execute(NavigationItem $item): void
    {
        $this->transaction(function () use ($item): void {
            $handle = $item->navigation->handle;
            $item->delete();
            $this->cache->forget("navigation:{$handle}");
        });
    }
}
