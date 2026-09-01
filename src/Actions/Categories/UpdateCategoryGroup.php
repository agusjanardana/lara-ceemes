<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Categories;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\CategoryGroup;
use LaraCeemes\Support\CeemesCache;

final class UpdateCategoryGroup extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(CategoryGroup $categoryGroup, array $data): CategoryGroup
    {
        $validated = Validator::make($data, [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'handle' => [
                'sometimes', 'required', 'alpha_dash:ascii', 'max:255',
                Rule::unique('ceemes_category_groups', 'handle')->ignore($categoryGroup->uuid, 'uuid'),
            ],
            'description' => ['nullable', 'string'],
        ])->validate();

        return $this->transaction(function () use ($categoryGroup, $validated): CategoryGroup {
            $oldHandle = $categoryGroup->handle;
            $categoryGroup->update($validated);
            $this->cache->forget("categoryGroup:{$oldHandle}");
            $this->cache->forget("categoryGroup:{$categoryGroup->handle}");

            return $categoryGroup->refresh();
        });
    }
}
