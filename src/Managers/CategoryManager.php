<?php

declare(strict_types=1);

namespace LaraCeemes\Managers;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use LaraCeemes\Models\Category;
use LaraCeemes\Models\CategoryGroup;

final class CategoryManager extends Manager
{
    public function get(string $handle): CategoryGroup
    {
        return $this->cache->remember(
            "categoryGroup:{$handle}",
            fn (): CategoryGroup => CategoryGroup::query()->where('handle', $handle)->first()
                ?? throw (new ModelNotFoundException)->setModel(CategoryGroup::class, [$handle]),
        );
    }

    /** @return EloquentCollection<int, Category> */
    public function categories(string $handle): EloquentCollection
    {
        return $this->get($handle)->categories()->get();
    }

    public function category(string $groupHandle, string $slug): ?Category
    {
        return $this->get($groupHandle)->categories()->where('slug', $slug)->first();
    }
}
