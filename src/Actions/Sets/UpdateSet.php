<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sets;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Events\SetUpdated;
use LaraCeemes\Models\Set;
use LaraCeemes\Support\ContentCacheInvalidator;
use LaraCeemes\Support\SetViewScaffolder;

final class UpdateSet extends Action
{
    public function __construct(
        private readonly ContentCacheInvalidator $cache,
        private readonly SetViewScaffolder $views,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(Set $set, array $data): Set
    {
        $mode = is_string($data['template_mode'] ?? null) ? $data['template_mode'] : null;
        $handle = (string) ($data['handle'] ?? $set->handle);

        if ($mode === 'auto') {
            $data['template'] = $this->views->viewName($handle);
        }

        if (in_array($mode, ['auto', 'custom'], true)) {
            $data['route'] = filled($data['route'] ?? null)
                ? $data['route']
                : $this->views->routePattern($handle);
        }

        if ($mode === 'none') {
            $data['template'] = null;
            $data['route'] = null;
            $data['is_publishable'] = false;
        }

        $validated = Validator::make($data, [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'handle' => [
                'sometimes',
                'required',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('ceemes_sets', 'handle')->ignore($set->uuid, 'uuid'),
            ],
            'description' => ['nullable', 'string'],
            'route' => ['nullable', 'string', 'max:255'],
            'template_mode' => ['sometimes', Rule::in(['auto', 'custom', 'none'])],
            'template' => ['nullable', 'required_if:template_mode,custom', 'string', 'max:255'],
            'is_publishable' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();

        unset($validated['template_mode']);

        return $this->transaction(function () use ($set, $validated, $mode): Set {
            $oldHandle = $set->handle;
            $set->update($validated);

            if ($mode === 'auto') {
                $this->views->scaffold($set);
            }

            $this->cache->set($oldHandle);
            $this->cache->set($set->handle);
            event(new SetUpdated($set));

            return $set->refresh();
        });
    }
}
