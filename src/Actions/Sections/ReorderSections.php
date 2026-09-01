<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Content;
use LaraCeemes\Support\ContentCacheInvalidator;

final class ReorderSections extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    /** @param array<int, string> $sectionUuids */
    public function execute(Content $content, array $sectionUuids, string $fieldHandle = 'sections'): void
    {
        Validator::make([
            'sections' => $sectionUuids,
            'field_handle' => $fieldHandle,
        ], [
            'sections' => ['array'],
            'sections.*' => ['required', 'uuid', 'distinct'],
            'field_handle' => ['required', 'alpha_dash:ascii'],
        ])->validate();

        $current = $content->placedSections()
            ->wherePivot('region', $fieldHandle)
            ->pluck('ceemes_sections.uuid')
            ->all();

        if (array_diff($current, $sectionUuids) !== [] || array_diff($sectionUuids, $current) !== []) {
            throw ValidationException::withMessages([
                'sections' => 'The order must contain every Section in the field exactly once.',
            ]);
        }

        $this->transaction(function () use ($content, $sectionUuids): void {
            foreach ($sectionUuids as $sortOrder => $uuid) {
                $content->placedSections()->updateExistingPivot($uuid, ['sort_order' => $sortOrder]);
            }

            $this->cache->content($content);
        });
    }
}
