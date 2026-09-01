<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sets;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Set;
use LaraCeemes\Support\ContentCacheInvalidator;

final class ReorderSetFields extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    /** @param array<int, string> $fieldUuids */
    public function execute(Set $set, array $fieldUuids): void
    {
        Validator::make(['fields' => $fieldUuids], ['fields' => ['array'], 'fields.*' => ['required', 'uuid', 'distinct']])->validate();
        $current = $set->fields()->pluck('uuid')->all();

        if (array_diff($current, $fieldUuids) !== [] || array_diff($fieldUuids, $current) !== []) {
            throw ValidationException::withMessages(['fields' => 'The field order must contain every Set Field exactly once.']);
        }

        $this->transaction(function () use ($set, $fieldUuids): void {
            foreach ($fieldUuids as $sortOrder => $uuid) {
                $set->fields()->where('uuid', $uuid)->update(['sort_order' => $sortOrder]);
            }
            $this->cache->set($set->handle);
        });
    }
}
