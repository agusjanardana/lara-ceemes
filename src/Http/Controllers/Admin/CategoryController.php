<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Categories\CreateCategory;
use LaraCeemes\Actions\Categories\DeleteCategory;
use LaraCeemes\Actions\Categories\UpdateCategory;
use LaraCeemes\Models\Category;
use LaraCeemes\Models\CategoryGroup;

final class CategoryController extends AdminController
{
    public function index(CategoryGroup $categoryGroup): View
    {
        $options = ['' => 'No parent'] + $categoryGroup->categories()->pluck('name', 'uuid')->all();
        $fields = [
            ['name' => 'name', 'label' => 'Name'],
            ['name' => 'slug', 'label' => 'Slug'],
            ['name' => 'parent_uuid', 'label' => 'Parent', 'type' => 'select', 'options' => $options],
            ['name' => 'data', 'label' => 'Data JSON', 'type' => 'textarea', 'default' => '{}'],
            ['name' => 'sort_order', 'label' => 'Sort order', 'type' => 'number', 'default' => 0],
        ];

        return $this->render('ceemes::admin.resource', [
            'title' => "Categories: {$categoryGroup->name}",
            'backUrl' => route('ceemes.admin.category-groups.index'),
            'storeUrl' => route('ceemes.admin.categories.store', $categoryGroup),
            'fields' => $fields,
            'resources' => $categoryGroup->categories()->get()->map(fn (Category $category): array => [
                'values' => [
                    ...$category->only(['name', 'slug', 'parent_uuid', 'sort_order']),
                    'data' => json_encode($category->data ?? [], JSON_PRETTY_PRINT),
                ],
                'update_url' => route('ceemes.admin.categories.update', $category),
                'delete_url' => route('ceemes.admin.categories.destroy', $category),
            ]),
        ]);
    }

    public function store(Request $request, CategoryGroup $categoryGroup, CreateCategory $action): RedirectResponse
    {
        $data = $request->all();
        $data['parent_uuid'] = $request->input('parent_uuid') ?: null;
        $data['data'] = $this->jsonObject($request->input('data'), 'data');
        $action->execute($categoryGroup, $data);

        return $this->success('ceemes.admin.categories.index', 'Category created.', $categoryGroup);
    }

    public function update(Request $request, Category $category, UpdateCategory $action): RedirectResponse
    {
        $data = $request->all();
        $data['parent_uuid'] = $request->input('parent_uuid') ?: null;
        $data['data'] = $this->jsonObject($request->input('data'), 'data');
        $action->execute($category, $data);

        return $this->success('ceemes.admin.categories.index', 'Category updated.', $category->categoryGroup);
    }

    public function destroy(Category $category, DeleteCategory $action): RedirectResponse
    {
        $categoryGroup = $category->categoryGroup;
        $action->execute($category);

        return $this->success('ceemes.admin.categories.index', 'Category deleted.', $categoryGroup);
    }
}
