<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $uuid
 * @property string $name
 * @property string $handle
 * @property string|null $description
 * @property string|null $route
 * @property string|null $template
 * @property bool $is_publishable
 * @property int $sort_order
 */
final class Collection extends CeemesModel
{
    protected $table = 'ceemes_collections';

    protected $fillable = [
        'name',
        'handle',
        'description',
        'route',
        'template',
        'is_publishable',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_publishable' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return HasMany<Blueprint, $this> */
    public function blueprints(): HasMany
    {
        return $this->hasMany(Blueprint::class, 'collection_uuid', 'uuid');
    }

    /** @return HasMany<Entry, $this> */
    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class, 'collection_uuid', 'uuid');
    }
}
