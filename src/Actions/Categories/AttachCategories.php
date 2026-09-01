<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Categories;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Category;
use LaraCeemes\Models\Content;
use LaraCeemes\Support\ContentCacheInvalidator;

final class AttachCategories extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    /** @param array<int, string> $categoryUuids */
    public function execute(Content $content, string $fieldHandle, array $categoryUuids): void
    {
        Validator::make([
            'field_handle' => $fieldHandle,
            'categories' => $categoryUuids,
        ], [
            'field_handle' => ['required', 'alpha_dash:ascii'],
            'categories' => ['array'],
            'categories.*' => ['uuid', 'distinct'],
        ])->validate();

        $field = $content->set->fields()
            ->where('handle', $fieldHandle)
            ->where('type', 'category')
            ->first();

        if ($field === null) {
            throw ValidationException::withMessages([
                'field_handle' => "Category field [{$fieldHandle}] does not exist on this Set.",
            ]);
        }

        $groupHandle = is_array($field->config) ? ($field->config['category_group'] ?? null) : null;
        $validCategories = Category::query()
            ->whereIn('uuid', $categoryUuids)
            ->when(is_string($groupHandle), fn ($query) => $query->whereHas(
                'categoryGroup',
                fn ($groupQuery) => $groupQuery->where('handle', $groupHandle),
            ))
            ->pluck('uuid')
            ->all();

        if (array_diff($categoryUuids, $validCategories) !== []) {
            throw ValidationException::withMessages([
                'categories' => 'One or more Categories do not belong to the configured Category Group.',
            ]);
        }

        $this->transaction(function () use ($content, $fieldHandle, $categoryUuids): void {
            $content->categoryRecords()->wherePivot('field_handle', $fieldHandle)->detach();

            foreach ($categoryUuids as $categoryUuid) {
                $content->categoryRecords()->attach($categoryUuid, ['field_handle' => $fieldHandle]);
            }

            $this->cache->content($content);
        });
    }
}
