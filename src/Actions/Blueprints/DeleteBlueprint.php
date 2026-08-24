<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Blueprints;

use LaraCeemes\Actions\Action;
use LaraCeemes\Exceptions\StructureInUse;
use LaraCeemes\Models\Blueprint;
use LaraCeemes\Models\Entry;
use LaraCeemes\Support\ContentCacheInvalidator;

final class DeleteBlueprint extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    public function execute(Blueprint $blueprint): void
    {
        if (Entry::withTrashed()->where('blueprint_uuid', $blueprint->uuid)->exists()) {
            throw StructureInUse::blueprint($blueprint->handle);
        }

        $this->transaction(function () use ($blueprint): void {
            $collectionHandle = $blueprint->collection->handle;
            $blueprint->delete();
            $this->cache->collection($collectionHandle);
        });
    }
}
