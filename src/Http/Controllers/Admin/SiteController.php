<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Sites\CreateSite;
use LaraCeemes\Actions\Sites\DeleteSite;
use LaraCeemes\Actions\Sites\UpdateSite;
use LaraCeemes\Models\Site;

final class SiteController extends AdminController
{
    public function index(): View
    {
        return $this->render('ceemes::admin.resource', [
            'title' => 'Sites',
            'storeUrl' => route('ceemes.admin.sites.store'),
            'fields' => $this->fields(),
            'resources' => Site::query()->orderBy('sort_order')->orderBy('name')->get()->map(fn (Site $site): array => [
                'values' => $site->only(['name', 'handle', 'locale', 'is_default', 'is_enabled', 'sort_order']),
                'update_url' => route('ceemes.admin.sites.update', $site),
                'delete_url' => route('ceemes.admin.sites.destroy', $site),
            ]),
        ]);
    }

    public function store(Request $request, CreateSite $action): RedirectResponse
    {
        $action->execute($request->all());

        return $this->success('ceemes.admin.sites.index', 'Site berhasil dibuat.');
    }

    public function update(Request $request, Site $site, UpdateSite $action): RedirectResponse
    {
        $action->execute($site, $request->all());

        return $this->success('ceemes.admin.sites.index', 'Site berhasil diperbarui.');
    }

    public function destroy(Site $site, DeleteSite $action): RedirectResponse
    {
        $action->execute($site);

        return $this->success('ceemes.admin.sites.index', 'Site berhasil dihapus.');
    }

    /** @return array<int, array<string, mixed>> */
    private function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'required' => true],
            ['name' => 'handle', 'label' => 'URL prefix', 'required' => true, 'help' => 'Contoh: en atau id.'],
            ['name' => 'locale', 'label' => 'Locale', 'required' => true, 'help' => 'Contoh: en, id, atau en-SG.'],
            ['name' => 'is_default', 'label' => 'Default Site', 'type' => 'checkbox'],
            ['name' => 'is_enabled', 'label' => 'Enabled', 'type' => 'checkbox', 'default' => true],
            ['name' => 'sort_order', 'label' => 'Sort order', 'type' => 'number', 'default' => 0],
        ];
    }
}
