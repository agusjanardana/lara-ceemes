<?php

declare(strict_types=1);

namespace LaraCeemes\Managers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use LaraCeemes\Models\Entry;

final class EntryManager extends Manager
{
    public function find(string $collectionHandle, string $slug): ?Entry
    {
        return $this->cache->remember(
            "entry:{$collectionHandle}:{$slug}",
            fn (): ?Entry => Entry::query()
                ->whereHas('collection', fn ($query) => $query->where('handle', $collectionHandle))
                ->where('slug', $slug)
                ->first(),
        );
    }

    public function findOrFail(string $collectionHandle, string $slug): Entry
    {
        return $this->find($collectionHandle, $slug)
            ?? throw (new ModelNotFoundException)->setModel(Entry::class, [$collectionHandle, $slug]);
    }

    public function byUuid(string $uuid): ?Entry
    {
        return Entry::query()->find($uuid);
    }
}
