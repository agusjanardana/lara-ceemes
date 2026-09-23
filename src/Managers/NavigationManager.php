<?php

declare(strict_types=1);

namespace LaraCeemes\Managers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Support\CeemesCache;
use LaraCeemes\Support\SiteContext;

final class NavigationManager extends Manager
{
    public function __construct(CeemesCache $cache, private readonly SiteContext $sites)
    {
        parent::__construct($cache);
    }

    public function get(string $handle): Navigation
    {
        return $this->cache->remember(
            $this->sites->cacheKey("navigation:{$handle}"),
            fn (): Navigation => Navigation::query()->where('handle', $handle)->first()
                ?? throw (new ModelNotFoundException)->setModel(Navigation::class, [$handle]),
        );
    }
}
