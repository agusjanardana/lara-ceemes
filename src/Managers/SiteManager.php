<?php

declare(strict_types=1);

namespace LaraCeemes\Managers;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use LaraCeemes\Models\Site;
use LaraCeemes\Support\CeemesCache;
use LaraCeemes\Support\SiteContext;

final class SiteManager extends Manager
{
    public function __construct(CeemesCache $cache, private readonly SiteContext $context)
    {
        parent::__construct($cache);
    }

    public function current(): Site
    {
        return $this->context->current();
    }

    public function use(string $handle): Site
    {
        return $this->context->useHandle($handle, enabledOnly: true);
    }

    /** @return EloquentCollection<int, Site> */
    public function all(bool $enabledOnly = true): EloquentCollection
    {
        return Site::query()
            ->when($enabledOnly, fn ($query) => $query->where('is_enabled', true))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
