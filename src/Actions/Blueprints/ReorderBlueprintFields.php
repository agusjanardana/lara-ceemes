<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Blueprints;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Blueprint;
use LaraCeemes\Support\ContentCacheInvalidator;

final class ReorderBlueprintFields extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    /** @param array<int, string> $fieldUuids */
    public function execute(Blueprint $blueprint, array $fieldUuids): void
    {
        Validator::make(['fields' => $fieldUuids], [
            'fields' => ['array'],
            'fields.*' => ['required', 'uuid', 'distinct'],
        ])->validate();

        $current = $blueprint->fields()->pluck('uuid')->all();

        if (array_diff($current, $fieldUuids) !== [] || array_diff($fieldUuids, $current) !== []) {
            throw ValidationException::withMessages([
                'fields' => 'The field order must contain every Blueprint Field exactly once.',
            ]);
        }

        $this->transaction(function () use ($blueprint, $fieldUuids): void {
            foreach ($fieldUuids as $sortOrder => $uuid) {
                $blueprint->fields()->where('uuid', $uuid)->update(['sort_order' => $sortOrder]);
            }

            $this->cache->collection($blueprint->collection->handle);
        });
    }
}
