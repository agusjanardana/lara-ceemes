<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Taxonomies\CreateTerm;
use LaraCeemes\Actions\Taxonomies\DeleteTerm;
use LaraCeemes\Actions\Taxonomies\UpdateTerm;
use LaraCeemes\Models\Taxonomy;
use LaraCeemes\Models\Term;

final class TermController extends AdminController
{
    public function index(Taxonomy $taxonomy): View
    {
        $options = ['' => 'No parent'] + $taxonomy->terms()->pluck('name', 'uuid')->all();
        $fields = [
            ['name' => 'name', 'label' => 'Name'],
            ['name' => 'slug', 'label' => 'Slug'],
            ['name' => 'parent_uuid', 'label' => 'Parent', 'type' => 'select', 'options' => $options],
            ['name' => 'data', 'label' => 'Data JSON', 'type' => 'textarea', 'default' => '{}'],
            ['name' => 'sort_order', 'label' => 'Sort order', 'type' => 'number', 'default' => 0],
        ];

        return $this->render('ceemes::admin.resource', [
            'title' => "Terms: {$taxonomy->name}",
            'backUrl' => route('ceemes.admin.taxonomies.index'),
            'storeUrl' => route('ceemes.admin.terms.store', $taxonomy),
            'fields' => $fields,
            'resources' => $taxonomy->terms()->get()->map(fn (Term $term): array => [
                'values' => [
                    ...$term->only(['name', 'slug', 'parent_uuid', 'sort_order']),
                    'data' => json_encode($term->data ?? [], JSON_PRETTY_PRINT),
                ],
                'update_url' => route('ceemes.admin.terms.update', $term),
                'delete_url' => route('ceemes.admin.terms.destroy', $term),
            ]),
        ]);
    }

    public function store(Request $request, Taxonomy $taxonomy, CreateTerm $action): RedirectResponse
    {
        $data = $request->all();
        $data['parent_uuid'] = $request->input('parent_uuid') ?: null;
        $data['data'] = $this->jsonObject($request->input('data'), 'data');
        $action->execute($taxonomy, $data);

        return $this->success('ceemes.admin.terms.index', 'Term created.', $taxonomy);
    }

    public function update(Request $request, Term $term, UpdateTerm $action): RedirectResponse
    {
        $data = $request->all();
        $data['parent_uuid'] = $request->input('parent_uuid') ?: null;
        $data['data'] = $this->jsonObject($request->input('data'), 'data');
        $action->execute($term, $data);

        return $this->success('ceemes.admin.terms.index', 'Term updated.', $term->taxonomy);
    }

    public function destroy(Term $term, DeleteTerm $action): RedirectResponse
    {
        $taxonomy = $term->taxonomy;
        $action->execute($term);

        return $this->success('ceemes.admin.terms.index', 'Term deleted.', $taxonomy);
    }
}
