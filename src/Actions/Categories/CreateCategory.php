<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Categories;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Category;
use LaraCeemes\Models\CategoryGroup;
use LaraCeemes\Support\CeemesCache;

final class CreateCategory extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(CategoryGroup $categoryGroup, array $data): Category
    {
        $data['slug'] ??= Str::slug((string) ($data['name'] ?? ''));
        $validated = Validator::make($data, [
            'parent_uuid' => [
                'nullable',
                Rule::exists('ceemes_categories', 'uuid')->where('category_group_uuid', $categoryGroup->uuid),
            ],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('ceemes_categories', 'slug')->where('category_group_uuid', $categoryGroup->uuid),
            ],
            'data' => ['nullable', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();

        return $this->transaction(function () use ($categoryGroup, $validated): Category {
            $category = $categoryGroup->categories()->create($validated);
            $this->cache->forget("categoryGroup:{$categoryGroup->handle}");

            return $category;
        });
    }
}
