<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Taxonomies\CreateTaxonomy;
use LaraCeemes\Actions\Taxonomies\DeleteTaxonomy;
use LaraCeemes\Actions\Taxonomies\UpdateTaxonomy;
use LaraCeemes\Models\Taxonomy;

final class TaxonomyController extends AdminController
{
    public function index(): View
    {
        $fields = [
            ['name' => 'name', 'label' => 'Name'],
            ['name' => 'handle', 'label' => 'Handle'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
        ];

        return $this->render('ceemes::admin.resource', [
            'title' => 'Taxonomies',
            'storeUrl' => route('ceemes.admin.taxonomies.store'),
            'fields' => $fields,
            'resources' => Taxonomy::query()->orderBy('name')->get()->map(fn (Taxonomy $taxonomy): array => [
                'values' => $taxonomy->only(['name', 'handle', 'description']),
                'update_url' => route('ceemes.admin.taxonomies.update', $taxonomy),
                'delete_url' => route('ceemes.admin.taxonomies.destroy', $taxonomy),
                'links' => [['label' => 'Terms', 'url' => route('ceemes.admin.terms.index', $taxonomy)]],
            ]),
        ]);
    }

    public function store(Request $request, CreateTaxonomy $action): RedirectResponse
    {
        $action->execute($request->all());

        return $this->success('ceemes.admin.taxonomies.index', 'Taxonomy created.');
    }

    public function update(Request $request, Taxonomy $taxonomy, UpdateTaxonomy $action): RedirectResponse
    {
        $action->execute($taxonomy, $request->all());

        return $this->success('ceemes.admin.taxonomies.index', 'Taxonomy updated.');
    }

    public function destroy(Taxonomy $taxonomy, DeleteTaxonomy $action): RedirectResponse
    {
        $action->execute($taxonomy);

        return $this->success('ceemes.admin.taxonomies.index', 'Taxonomy deleted.');
    }
}
