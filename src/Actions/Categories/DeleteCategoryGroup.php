<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Categories;

use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\CategoryGroup;
use LaraCeemes\Support\CeemesCache;

final class DeleteCategoryGroup extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    public function execute(CategoryGroup $categoryGroup): void
    {
        if ($categoryGroup->categories()->exists()) {
            throw ValidationException::withMessages([
                'category_group' => "Category Group [{$categoryGroup->handle}] still contains Categories.",
            ]);
        }

        $this->transaction(function () use ($categoryGroup): void {
            $handle = $categoryGroup->handle;
            $categoryGroup->delete();
            $this->cache->forget("categoryGroup:{$handle}");
        });
    }
}
