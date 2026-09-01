<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Categories\CreateCategoryGroup;
use LaraCeemes\Actions\Categories\DeleteCategoryGroup;
use LaraCeemes\Actions\Categories\UpdateCategoryGroup;
use LaraCeemes\Models\CategoryGroup;

final class CategoryGroupController extends AdminController
{
    public function index(): View
    {
        $fields = [
            ['name' => 'name', 'label' => 'Name'],
            ['name' => 'handle', 'label' => 'Handle'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
        ];

        return $this->render('ceemes::admin.resource', [
            'title' => 'Category Groups',
            'storeUrl' => route('ceemes.admin.category-groups.store'),
            'fields' => $fields,
            'resources' => CategoryGroup::query()->orderBy('name')->get()->map(fn (CategoryGroup $categoryGroup): array => [
                'values' => $categoryGroup->only(['name', 'handle', 'description']),
                'update_url' => route('ceemes.admin.category-groups.update', $categoryGroup),
                'delete_url' => route('ceemes.admin.category-groups.destroy', $categoryGroup),
                'links' => [['label' => 'Categories', 'url' => route('ceemes.admin.categories.index', $categoryGroup)]],
            ]),
        ]);
    }

    public function store(Request $request, CreateCategoryGroup $action): RedirectResponse
    {
        $action->execute($request->all());

        return $this->success('ceemes.admin.category-groups.index', 'Category Group created.');
    }

    public function update(Request $request, CategoryGroup $categoryGroup, UpdateCategoryGroup $action): RedirectResponse
    {
        $action->execute($categoryGroup, $request->all());

        return $this->success('ceemes.admin.category-groups.index', 'Category Group updated.');
    }

    public function destroy(CategoryGroup $categoryGroup, DeleteCategoryGroup $action): RedirectResponse
    {
        $action->execute($categoryGroup);

        return $this->success('ceemes.admin.category-groups.index', 'Category Group deleted.');
    }
}
