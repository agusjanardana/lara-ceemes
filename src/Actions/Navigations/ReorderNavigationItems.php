<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Navigations;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Support\CeemesCache;

final class ReorderNavigationItems extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    /** @param array<int, string> $itemUuids */
    public function execute(Navigation $navigation, array $itemUuids, ?string $parentUuid = null): void
    {
        Validator::make(['items' => $itemUuids, 'parent_uuid' => $parentUuid], [
            'items' => ['array'],
            'items.*' => ['required', 'uuid', 'distinct'],
            'parent_uuid' => ['nullable', 'uuid'],
        ])->validate();

        $query = $navigation->itemRecords();
        $parentUuid === null ? $query->whereNull('parent_uuid') : $query->where('parent_uuid', $parentUuid);
        $current = $query->pluck('uuid')->all();

        if (array_diff($current, $itemUuids) !== [] || array_diff($itemUuids, $current) !== []) {
            throw ValidationException::withMessages([
                'items' => 'The order must contain every sibling Navigation Item exactly once.',
            ]);
        }

        $this->transaction(function () use ($navigation, $itemUuids): void {
            foreach ($itemUuids as $sortOrder => $uuid) {
                $navigation->itemRecords()->where('uuid', $uuid)->update(['sort_order' => $sortOrder]);
            }

            $this->cache->forget("navigation:{$navigation->handle}");
        });
    }
}
