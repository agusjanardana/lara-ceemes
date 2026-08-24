<?php

declare(strict_types=1);

namespace LaraCeemes\Managers;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use LaraCeemes\Exceptions\CollectionNotFound;
use LaraCeemes\Models\Collection;
use LaraCeemes\Queries\EntryQuery;

final class CollectionManager extends Manager
{
    public function get(string $handle): Collection
    {
        return $this->cache->remember(
            "collection:{$handle}",
            fn (): Collection => Collection::query()->where('handle', $handle)->first()
                ?? throw CollectionNotFound::forHandle($handle),
        );
    }

    public function find(string $handle): ?Collection
    {
        return Collection::query()->where('handle', $handle)->first();
    }

    public function exists(string $handle): bool
    {
        return Collection::query()->where('handle', $handle)->exists();
    }

    /** @return EloquentCollection<int, Collection> */
    public function all(): EloquentCollection
    {
        return $this->cache->remember(
            'collections',
            fn (): EloquentCollection => Collection::query()->orderBy('sort_order')->orderBy('name')->get(),
        );
    }

    public function query(string $handle): EntryQuery
    {
        return new EntryQuery($this->get($handle));
    }
}
