<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $uuid
 * @property string $name
 * @property string $handle
 */
final class Navigation extends CeemesModel
{
    protected $table = 'ceemes_navigations';

    protected $fillable = ['name', 'handle'];

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
