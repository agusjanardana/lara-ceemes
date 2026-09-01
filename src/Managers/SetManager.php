<?php

declare(strict_types=1);

namespace LaraCeemes\Managers;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use LaraCeemes\Exceptions\SetNotFound;
use LaraCeemes\Models\Set;
use LaraCeemes\Queries\ContentQuery;

final class SetManager extends Manager
{
    public function get(string $handle): Set
    {
        return $this->cache->remember(
            "set:{$handle}",
            fn (): Set => Set::query()->where('handle', $handle)->first()
                ?? throw SetNotFound::forHandle($handle),
        );
    }

    public function find(string $handle): ?Set
    {
        return Set::query()->where('handle', $handle)->first();
    }

    public function exists(string $handle): bool
    {
        return Set::query()->where('handle', $handle)->exists();
    }

    /** @return EloquentCollection<int, Set> */
    public function all(): EloquentCollection
    {
        return $this->cache->remember(
            'sets',
            fn (): EloquentCollection => Set::query()->orderBy('sort_order')->orderBy('name')->get(),
        );
    }

    public function query(string $handle): ContentQuery
    {
        return new ContentQuery($this->get($handle));
    }
}
