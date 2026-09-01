<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sets;

use LaraCeemes\Actions\Action;
use LaraCeemes\Exceptions\StructureInUse;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Set;
use LaraCeemes\Support\ContentCacheInvalidator;

final class DeleteSet extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    public function execute(Set $set): void
    {
        if (Content::withTrashed()->where('set_uuid', $set->uuid)->exists()) {
            throw StructureInUse::set($set->handle);
        }

        $this->transaction(function () use ($set): void {
            $handle = $set->handle;
            $set->delete();
            $this->cache->set($handle);
        });
    }
}
