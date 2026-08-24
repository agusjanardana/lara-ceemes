<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Taxonomies;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Entry;
use LaraCeemes\Models\Term;
use LaraCeemes\Support\ContentCacheInvalidator;

final class AttachTerms extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    /** @param array<int, string> $termUuids */
    public function execute(Entry $entry, string $fieldHandle, array $termUuids): void
    {
        Validator::make([
            'field_handle' => $fieldHandle,
            'terms' => $termUuids,
        ], [
            'field_handle' => ['required', 'alpha_dash:ascii'],
            'terms' => ['array'],
            'terms.*' => ['uuid', 'distinct'],
        ])->validate();

        $field = $entry->blueprint->fields()
            ->where('handle', $fieldHandle)
            ->where('type', 'taxonomy')
            ->first();

        if ($field === null) {
            throw ValidationException::withMessages([
                'field_handle' => "Taxonomy field [{$fieldHandle}] does not exist on this Blueprint.",
            ]);
        }

        $taxonomyHandle = is_array($field->config) ? ($field->config['taxonomy'] ?? null) : null;
        $validTerms = Term::query()
            ->whereIn('uuid', $termUuids)
            ->when(is_string($taxonomyHandle), fn ($query) => $query->whereHas(
                'taxonomy',
                fn ($taxonomyQuery) => $taxonomyQuery->where('handle', $taxonomyHandle),
            ))
            ->pluck('uuid')
            ->all();

        if (array_diff($termUuids, $validTerms) !== []) {
            throw ValidationException::withMessages([
                'terms' => 'One or more Terms do not belong to the configured Taxonomy.',
            ]);
        }

        $this->transaction(function () use ($entry, $fieldHandle, $termUuids): void {
            $entry->termRecords()->wherePivot('field_handle', $fieldHandle)->detach();

            foreach ($termUuids as $termUuid) {
                $entry->termRecords()->attach($termUuid, ['field_handle' => $fieldHandle]);
            }

            $this->cache->entry($entry);
        });
    }
}
