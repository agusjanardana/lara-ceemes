<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $uuid
 * @property string $name
 * @property string $handle
 * @property string|null $description
 */
class CategoryGroup extends CeemesModel
{
    protected $table = 'ceemes_category_groups';

    protected $fillable = ['name', 'handle', 'description'];

    /** @return HasMany<Category, $this> */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'category_group_uuid', 'uuid')->orderBy('sort_order');
    }
}
