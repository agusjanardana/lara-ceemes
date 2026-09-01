<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Categories;

use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Category;
use LaraCeemes\Support\CeemesCache;

final class DeleteCategory extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    public function execute(Category $category): void
    {
        $this->transaction(function () use ($category): void {
            $handle = $category->categoryGroup->handle;
            $category->delete();
            $this->cache->forget("categoryGroup:{$handle}");
        });
    }
}
