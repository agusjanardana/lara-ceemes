<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $uuid
 * @property string $category_group_uuid
 * @property string|null $parent_uuid
 * @property string $name
 * @property string $slug
 * @property array<string, mixed>|null $data
 * @property int $sort_order
 * @property-read CategoryGroup $categoryGroup
 */
class Category extends CeemesModel
{
    protected $table = 'ceemes_categories';

    protected $fillable = [
        'category_group_uuid',
        'parent_uuid',
        'name',
        'slug',
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

    /** @return BelongsTo<CategoryGroup, $this> */
    public function categoryGroup(): BelongsTo
    {
        return $this->belongsTo(CategoryGroup::class, 'category_group_uuid', 'uuid');
    }

    /** @return BelongsTo<Category, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_uuid', 'uuid');
    }

    /** @return HasMany<Category, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_uuid', 'uuid')->orderBy('sort_order');
    }

    /** @return BelongsToMany<Content, $this> */
    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(
            Content::class,
            'ceemes_content_category',
            'category_uuid',
            'content_uuid',
            'uuid',
            'uuid',
        )->withPivot('field_handle');
    }
}
