<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Categories;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Category;
use LaraCeemes\Support\CeemesCache;

final class UpdateCategory extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(Category $category, array $data): Category
    {
        $validated = Validator::make($data, [
            'parent_uuid' => [
                'nullable',
                Rule::exists('ceemes_categories', 'uuid')->where('category_group_uuid', $category->category_group_uuid),
                Rule::notIn([$category->uuid]),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes', 'required', 'alpha_dash:ascii', 'max:255',
                Rule::unique('ceemes_categories', 'slug')
                    ->where('category_group_uuid', $category->category_group_uuid)
                    ->ignore($category->uuid, 'uuid'),
            ],
            'data' => ['nullable', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();

        return $this->transaction(function () use ($category, $validated): Category {
            $category->update($validated);
            $this->cache->forget("categoryGroup:{$category->categoryGroup->handle}");

            return $category->refresh();
        });
    }
}
