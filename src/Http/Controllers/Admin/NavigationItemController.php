<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Navigations\CreateNavigationItem;
use LaraCeemes\Actions\Navigations\DeleteNavigationItem;
use LaraCeemes\Actions\Navigations\UpdateNavigationItem;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Models\NavigationItem;

final class NavigationItemController extends AdminController
{
    public function index(Navigation $navigation): View
    {
        $items = $this->flatten($navigation->items());
        $options = ['' => 'No parent'] + collect($items)->mapWithKeys(
            fn (array $item): array => [$item['model']->uuid => str_repeat('— ', $item['depth']).$item['model']->label],
        )->all();
        $fields = [
            ['name' => 'label', 'label' => 'Label'],
            ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => ['url' => 'URL', 'route' => 'Named route', 'content' => 'Content UUID']],
            ['name' => 'target', 'label' => 'Target'],
            ['name' => 'parent_uuid', 'label' => 'Parent', 'type' => 'select', 'options' => $options],
            ['name' => 'data', 'label' => 'Data JSON', 'type' => 'textarea', 'default' => '{}'],
            ['name' => 'sort_order', 'label' => 'Sort order', 'type' => 'number', 'default' => 0],
        ];

        return $this->render('ceemes::admin.resource', [
            'title' => "Navigation Items: {$navigation->name}",
            'backUrl' => route('ceemes.admin.navigations.index'),
            'storeUrl' => route('ceemes.admin.navigation-items.store', $navigation),
            'fields' => $fields,
            'tree' => true,
            'resources' => collect($items)->map(fn (array $treeItem): array => [
                'values' => [
                    ...$treeItem['model']->only(['label', 'type', 'target', 'parent_uuid', 'sort_order']),
                    'data' => json_encode($treeItem['model']->data ?? [], JSON_PRETTY_PRINT),
                ],
                'depth' => $treeItem['depth'],
                'update_url' => route('ceemes.admin.navigation-items.update', $treeItem['model']),
                'delete_url' => route('ceemes.admin.navigation-items.destroy', $treeItem['model']),
            ]),
        ]);
    }

    public function store(Request $request, Navigation $navigation, CreateNavigationItem $action): RedirectResponse
    {
        $data = $request->all();
        $data['parent_uuid'] = $request->input('parent_uuid') ?: null;
        $data['data'] = $this->jsonObject($request->input('data'), 'data');
        $action->execute($navigation, $data);

        return $this->success('ceemes.admin.navigation-items.index', 'Navigation Item created.', $navigation);
    }

    public function update(Request $request, NavigationItem $navigationItem, UpdateNavigationItem $action): RedirectResponse
    {
        $data = $request->all();
        $data['parent_uuid'] = $request->input('parent_uuid') ?: null;
        $data['data'] = $this->jsonObject($request->input('data'), 'data');
        $action->execute($navigationItem, $data);

        return $this->success('ceemes.admin.navigation-items.index', 'Navigation Item updated.', $navigationItem->navigation);
    }

    public function destroy(NavigationItem $navigationItem, DeleteNavigationItem $action): RedirectResponse
    {
        $navigation = $navigationItem->navigation;
        $action->execute($navigationItem);

        return $this->success('ceemes.admin.navigation-items.index', 'Navigation Item deleted.', $navigation);
    }

    /**
     * @param  EloquentCollection<int, NavigationItem>  $items
     * @return array<int, array{model: NavigationItem, depth: int}>
     */
    private function flatten(EloquentCollection $items, int $depth = 0): array
    {
        $flattened = [];

        foreach ($items as $item) {
            $flattened[] = ['model' => $item, 'depth' => $depth];
            $flattened = [
                ...$flattened,
                ...$this->flatten($item->childrenRecursive, $depth + 1),
            ];
        }

        return $flattened;
    }
}
