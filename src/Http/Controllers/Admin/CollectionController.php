<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Collections\CreateCollection;
use LaraCeemes\Actions\Collections\DeleteCollection;
use LaraCeemes\Actions\Collections\UpdateCollection;
use LaraCeemes\Models\Collection;

final class CollectionController extends AdminController
{
    public function index(): View
    {
        return $this->render('ceemes::admin.resource', [
            'title' => 'Collections',
            'storeUrl' => route('ceemes.admin.collections.store'),
            'fields' => $this->fields(),
            'resources' => Collection::query()->orderBy('sort_order')->get()->map(fn (Collection $collection): array => [
                'values' => $collection->only(['name', 'handle', 'description', 'route', 'template', 'is_publishable']),
                'update_url' => route('ceemes.admin.collections.update', $collection),
                'delete_url' => route('ceemes.admin.collections.destroy', $collection),
                'links' => [
                    ['label' => 'Blueprints', 'url' => route('ceemes.admin.blueprints.index', $collection)],
                    ['label' => 'Entries', 'url' => route('ceemes.admin.entries.index', $collection)],
                ],
            ]),
        ]);
    }

    public function store(Request $request, CreateCollection $action): RedirectResponse
    {
        $action->execute($request->all());

        return $this->success('ceemes.admin.collections.index', 'Collection created.');
    }

    public function update(Request $request, Collection $collection, UpdateCollection $action): RedirectResponse
    {
        $action->execute($collection, $request->all());

        return $this->success('ceemes.admin.collections.index', 'Collection updated.');
    }

    public function destroy(Collection $collection, DeleteCollection $action): RedirectResponse
    {
        $action->execute($collection);

        return $this->success('ceemes.admin.collections.index', 'Collection deleted.');
    }

    /** @return array<int, array<string, mixed>> */
    private function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name'],
            ['name' => 'handle', 'label' => 'Handle'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ['name' => 'route', 'label' => 'Default URL pattern', 'placeholder' => '/{slug}', 'help' => 'Dipakai untuk membuat Public URL awal setiap Entry.'],
            ['name' => 'template', 'label' => 'Frontend Blade view', 'placeholder' => 'pages.show', 'help' => 'Opsional. Jika kosong, gunakan tampilan bawaan Lara Ceemes.'],
            ['name' => 'is_publishable', 'label' => 'Publishable', 'type' => 'checkbox', 'default' => true],
        ];
    }
}
