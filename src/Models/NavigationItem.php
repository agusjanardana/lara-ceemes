<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;

/**
 * @property string $uuid
 * @property string $navigation_uuid
 * @property string|null $parent_uuid
 * @property string $label
 * @property string $type
 * @property string $target
 * @property array<string, mixed>|null $data
 * @property int $sort_order
 * @property-read Navigation $navigation
 */
final class NavigationItem extends CeemesModel
{
    protected $table = 'ceemes_navigation_items';

    protected $fillable = [
        'navigation_uuid',
        'parent_uuid',
        'label',
        'type',
        'target',
        'data',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<Navigation, $this> */
    public function navigation(): BelongsTo
    {
        return $this->belongsTo(Navigation::class, 'navigation_uuid', 'uuid');
    }

    /** @return BelongsTo<NavigationItem, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_uuid', 'uuid');
    }

    /** @return HasMany<NavigationItem, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_uuid', 'uuid')->orderBy('sort_order');
    }

    /** @return HasMany<NavigationItem, $this> */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function resolvedUrl(): ?string
    {
        if ($this->type === 'url') {
            return $this->target;
        }

        if ($this->type === 'route') {
            return Route::has($this->target) ? route($this->target) : null;
        }

        return Entry::query()->find($this->target)?->publicUrl();
    }
}
