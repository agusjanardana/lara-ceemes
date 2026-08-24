<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Collections;

use LaraCeemes\Actions\Action;
use LaraCeemes\Exceptions\StructureInUse;
use LaraCeemes\Models\Collection;
use LaraCeemes\Models\Entry;
use LaraCeemes\Support\ContentCacheInvalidator;

final class DeleteCollection extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    public function execute(Collection $collection): void
    {
        if ($collection->blueprints()->exists()
            || Entry::withTrashed()->where('collection_uuid', $collection->uuid)->exists()) {
            throw StructureInUse::collection($collection->handle);
        }

        $this->transaction(function () use ($collection): void {
            $handle = $collection->handle;
            $collection->delete();
            $this->cache->collection($handle);
        });
    }
}
