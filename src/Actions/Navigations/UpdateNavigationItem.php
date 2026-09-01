<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Navigations;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\NavigationItem;
use LaraCeemes\Support\CeemesCache;

final class UpdateNavigationItem extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(NavigationItem $item, array $data): NavigationItem
    {
        $type = is_string($data['type'] ?? null) ? $data['type'] : $item->type;
        $targetRules = match ($type) {
            'content' => ['sometimes', 'required', 'uuid', Rule::exists('ceemes_contents', 'uuid')],
            'url' => ['sometimes', 'required', 'url'],
            default => ['sometimes', 'required', 'string', 'max:255'],
        };
        $validated = Validator::make($data, [
            'parent_uuid' => [
                'nullable',
                Rule::notIn([$item->uuid]),
                Rule::exists('ceemes_navigation_items', 'uuid')->where('navigation_uuid', $item->navigation_uuid),
            ],
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', Rule::in(['content', 'url', 'route'])],
            'target' => $targetRules,
            'data' => ['nullable', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();

        if (isset($validated['parent_uuid']) && $this->isDescendant($item, $validated['parent_uuid'])) {
            throw ValidationException::withMessages([
                'parent_uuid' => 'A Navigation Item cannot be moved below one of its descendants.',
            ]);
        }

        return $this->transaction(function () use ($item, $validated): NavigationItem {
            $item->update($validated);
            $this->cache->forget("navigation:{$item->navigation->handle}");

            return $item->refresh();
        });
    }

    private function isDescendant(NavigationItem $item, string $candidateUuid): bool
    {
        $candidate = NavigationItem::query()->find($candidateUuid);

        while ($candidate !== null) {
            if ($candidate->parent_uuid === $item->uuid) {
                return true;
            }

            $candidate = $candidate->parent_uuid !== null
                ? NavigationItem::query()->find($candidate->parent_uuid)
                : null;
        }

        return false;
    }
}
