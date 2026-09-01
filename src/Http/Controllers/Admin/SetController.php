<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Sets\CreateSet;
use LaraCeemes\Actions\Sets\DeleteSet;
use LaraCeemes\Actions\Sets\UpdateSet;
use LaraCeemes\Models\Set;
use LaraCeemes\Support\SetViewScaffolder;

final class SetController extends AdminController
{
    public function __construct(private readonly SetViewScaffolder $views) {}

    public function index(): View
    {
        return $this->render('ceemes::admin.resource', [
            'title' => 'Sets',
            'storeUrl' => route('ceemes.admin.sets.store'),
            'fields' => $this->fields(),
            'resources' => Set::query()->orderBy('sort_order')->get()->map(fn (Set $set): array => [
                'values' => [
                    ...$set->only(['name', 'handle', 'description', 'route', 'template', 'is_publishable']),
                    'template_mode' => $this->templateMode($set),
                ],
                'update_url' => route('ceemes.admin.sets.update', $set),
                'delete_url' => route('ceemes.admin.sets.destroy', $set),
                'links' => [
                    ['label' => 'Contents', 'url' => route('ceemes.admin.contents.index', $set)],
                ],
            ]),
        ]);
    }

    public function store(Request $request, CreateSet $action): RedirectResponse
    {
        $action->execute($request->all());

        return $this->success('ceemes.admin.sets.index', 'Set created.');
    }

    public function update(Request $request, Set $set, UpdateSet $action): RedirectResponse
    {
        $action->execute($set, $request->all());

        return $this->success('ceemes.admin.sets.index', 'Set updated.');
    }

    public function destroy(Set $set, DeleteSet $action): RedirectResponse
    {
        $action->execute($set);

        return $this->success('ceemes.admin.sets.index', 'Set deleted.');
    }

    /** @return array<int, array<string, mixed>> */
    private function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name'],
            ['name' => 'handle', 'label' => 'Handle'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ['name' => 'template_mode', 'label' => 'Frontend template', 'type' => 'select', 'default' => 'auto', 'options' => [
                'auto' => 'Buat Blade otomatis',
                'custom' => 'Gunakan Blade custom',
                'none' => 'Tanpa frontend (data only)',
            ], 'help' => 'Otomatis: Pages memakai pages.blade.php; Set lain memakai {handle}/show.blade.php.'],
            ['name' => 'route', 'label' => 'Default URL pattern', 'placeholder' => '/products/{slug}', 'help' => 'Boleh dikosongkan agar mengikuti convention Set.'],
            ['name' => 'template', 'label' => 'Custom Blade view', 'placeholder' => 'store.products.show', 'help' => 'Hanya digunakan saat mode custom. Gunakan nama view tanpa .blade.php.'],
            ['name' => 'is_publishable', 'label' => 'Publishable', 'type' => 'checkbox', 'default' => true],
        ];
    }

    private function templateMode(Set $set): string
    {
        if ($set->template === null && ! $set->is_publishable) {
            return 'none';
        }

        return $this->views->isConvention($set) ? 'auto' : 'custom';
    }
}
