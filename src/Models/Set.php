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
class Set extends CeemesModel
{
    protected $table = 'ceemes_sets';

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

    /** @return HasMany<Content, $this> */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class, 'set_uuid', 'uuid');
    }

    /** @return HasMany<SetField, $this> */
    public function fields(): HasMany
    {
        return $this->hasMany(SetField::class, 'set_uuid', 'uuid')->orderBy('sort_order');
    }
}
