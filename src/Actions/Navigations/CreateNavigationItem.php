<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Navigations;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Models\NavigationItem;
use LaraCeemes\Support\CeemesCache;

final class CreateNavigationItem extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(Navigation $navigation, array $data): NavigationItem
    {
        $validated = $this->validate($navigation, $data);

        return $this->transaction(function () use ($navigation, $validated): NavigationItem {
            $item = $navigation->itemRecords()->create($validated);
            $this->cache->forget("site:{$navigation->site_uuid}:navigation:{$navigation->handle}");

            return $item;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function validate(Navigation $navigation, array $data): array
    {
        $type = is_string($data['type'] ?? null) ? $data['type'] : '';
        $targetRules = match ($type) {
            'content' => [
                'required',
                'uuid',
                Rule::exists('ceemes_contents', 'uuid')->where('site_uuid', $navigation->site_uuid),
            ],
            'url' => ['required', 'url'],
            default => ['required', 'string', 'max:255'],
        };

        return Validator::make($data, [
            'parent_uuid' => [
                'nullable',
                Rule::exists('ceemes_navigation_items', 'uuid')->where('navigation_uuid', $navigation->uuid),
            ],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['content', 'url', 'route'])],
            'target' => $targetRules,
            'data' => ['nullable', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();
    }
}
