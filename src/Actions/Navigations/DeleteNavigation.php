<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Navigations;

use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Support\CeemesCache;

final class DeleteNavigation extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    public function execute(Navigation $navigation): void
    {
        $this->transaction(function () use ($navigation): void {
            $handle = $navigation->handle;
            $navigation->delete();
            $this->cache->forget("site:{$navigation->site_uuid}:navigation:{$handle}");
        });
    }
}
