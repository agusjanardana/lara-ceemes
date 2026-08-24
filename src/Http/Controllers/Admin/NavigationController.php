<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Navigations\CreateNavigation;
use LaraCeemes\Actions\Navigations\DeleteNavigation;
use LaraCeemes\Actions\Navigations\UpdateNavigation;
use LaraCeemes\Models\Navigation;

final class NavigationController extends AdminController
{
    public function index(): View
    {
        $fields = [['name' => 'name', 'label' => 'Name'], ['name' => 'handle', 'label' => 'Handle']];

        return $this->render('ceemes::admin.resource', [
            'title' => 'Navigations',
            'storeUrl' => route('ceemes.admin.navigations.store'),
            'fields' => $fields,
            'resources' => Navigation::query()->orderBy('name')->get()->map(fn (Navigation $navigation): array => [
                'values' => $navigation->only(['name', 'handle']),
                'update_url' => route('ceemes.admin.navigations.update', $navigation),
                'delete_url' => route('ceemes.admin.navigations.destroy', $navigation),
                'links' => [['label' => 'Items', 'url' => route('ceemes.admin.navigation-items.index', $navigation)]],
            ]),
        ]);
    }

    public function store(Request $request, CreateNavigation $action): RedirectResponse
    {
        $action->execute($request->all());

        return $this->success('ceemes.admin.navigations.index', 'Navigation created.');
    }

    public function update(Request $request, Navigation $navigation, UpdateNavigation $action): RedirectResponse
    {
        $action->execute($navigation, $request->all());

        return $this->success('ceemes.admin.navigations.index', 'Navigation updated.');
    }

    public function destroy(Navigation $navigation, DeleteNavigation $action): RedirectResponse
    {
        $action->execute($navigation);

        return $this->success('ceemes.admin.navigations.index', 'Navigation deleted.');
    }
}
