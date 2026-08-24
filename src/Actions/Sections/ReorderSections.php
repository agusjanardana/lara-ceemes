<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Entry;
use LaraCeemes\Support\ContentCacheInvalidator;

final class ReorderSections extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    /** @param array<int, string> $sectionUuids */
    public function execute(Entry $entry, array $sectionUuids, string $fieldHandle = 'sections'): void
    {
        Validator::make([
            'sections' => $sectionUuids,
            'field_handle' => $fieldHandle,
        ], [
            'sections' => ['array'],
            'sections.*' => ['required', 'uuid', 'distinct'],
            'field_handle' => ['required', 'alpha_dash:ascii'],
        ])->validate();

        $current = $entry->sectionItems()
            ->where('field_handle', $fieldHandle)
            ->pluck('uuid')
            ->all();

        if (array_diff($current, $sectionUuids) !== [] || array_diff($sectionUuids, $current) !== []) {
            throw ValidationException::withMessages([
                'sections' => 'The order must contain every Section in the field exactly once.',
            ]);
        }

        $this->transaction(function () use ($entry, $sectionUuids): void {
            foreach ($sectionUuids as $sortOrder => $uuid) {
                $entry->sectionItems()->where('uuid', $uuid)->update(['sort_order' => $sortOrder]);
            }

            $this->cache->entry($entry);
        });
    }
}
