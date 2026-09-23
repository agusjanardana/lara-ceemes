<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LaraCeemes\Models\Concerns\BelongsToSite;

/**
 * @property string $uuid
 * @property string $name
 * @property string $handle
 * @property string $site_uuid
 */
final class Navigation extends CeemesModel
{
    use BelongsToSite;

    protected $table = 'ceemes_navigations';

    protected $fillable = ['site_uuid', 'name', 'handle'];

    /** @return HasMany<NavigationItem, $this> */
    public function itemRecords(): HasMany
    {
        return $this->hasMany(NavigationItem::class, 'navigation_uuid', 'uuid');
    }

    /** @return EloquentCollection<int, NavigationItem> */
    public function items(): EloquentCollection
    {
        return $this->itemRecords()
            ->whereNull('parent_uuid')
            ->with('childrenRecursive')
            ->orderBy('sort_order')
            ->get();
    }
}
