<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sets;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Events\SetCreated;
use LaraCeemes\Models\Set;
use LaraCeemes\Support\ContentCacheInvalidator;
use LaraCeemes\Support\SetViewScaffolder;

final class CreateSet extends Action
{
    public function __construct(
        private readonly ContentCacheInvalidator $cache,
        private readonly SetViewScaffolder $views,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data): Set
    {
        $data['handle'] ??= Str::slug((string) ($data['name'] ?? ''));
        $mode = is_string($data['template_mode'] ?? null)
            ? $data['template_mode']
            : (filled($data['template'] ?? null) ? 'custom' : 'auto');
        $data['template_mode'] = $mode;

        if ($mode === 'auto') {
            $data['template'] = $this->views->viewName((string) $data['handle']);
        }

        if (in_array($mode, ['auto', 'custom'], true)) {
            $data['route'] = filled($data['route'] ?? null)
                ? $data['route']
                : $this->views->routePattern((string) $data['handle']);
        }

        if ($mode === 'none') {
            $data['template'] = null;
            $data['route'] = null;
            $data['is_publishable'] = false;
        }

        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['required', 'alpha_dash:ascii', 'max:255', Rule::unique('ceemes_sets', 'handle')],
            'description' => ['nullable', 'string'],
            'route' => ['nullable', 'string', 'max:255'],
            'template_mode' => ['required', Rule::in(['auto', 'custom', 'none'])],
            'template' => ['nullable', 'required_if:template_mode,custom', 'string', 'max:255'],
            'is_publishable' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();

        unset($validated['template_mode']);

        return $this->transaction(function () use ($validated, $mode): Set {
            $set = Set::query()->create($validated);

            if ($mode === 'auto') {
                $this->views->scaffold($set);
            }

            $this->cache->set($set->handle);
            event(new SetCreated($set));

            return $set->refresh();
        });
    }
}
