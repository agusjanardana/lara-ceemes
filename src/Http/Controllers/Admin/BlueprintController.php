<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Blueprints\CreateBlueprint;
use LaraCeemes\Actions\Blueprints\DeleteBlueprint;
use LaraCeemes\Actions\Blueprints\UpdateBlueprint;
use LaraCeemes\Models\Blueprint;
use LaraCeemes\Models\Collection;

final class BlueprintController extends AdminController
{
    public function index(Collection $collection): View
    {
        return $this->render('ceemes::admin.blueprints.index', [
            'collection' => $collection,
            'blueprints' => $collection->blueprints()->withCount(['fields', 'entries'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Collection $collection, CreateBlueprint $action): RedirectResponse
    {
        $blueprint = $action->execute($collection, $request->all());

        return $this->success('ceemes.admin.fields.index', 'Blueprint dibuat. Sekarang tambahkan field yang dibutuhkan.', $blueprint);
    }

    public function update(Request $request, Blueprint $blueprint, UpdateBlueprint $action): RedirectResponse
    {
        $action->execute($blueprint, $request->all());

        return $this->success('ceemes.admin.blueprints.index', 'Blueprint updated.', $blueprint->collection);
    }

    public function destroy(Blueprint $blueprint, DeleteBlueprint $action): RedirectResponse
    {
        $collection = $blueprint->collection;
        $action->execute($blueprint);

        return $this->success('ceemes.admin.blueprints.index', 'Blueprint deleted.', $collection);
    }
}
