<?php

declare(strict_types=1);

namespace LaraCeemes\Managers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use LaraCeemes\Models\Content;

final class ContentManager extends Manager
{
    public function find(string $setHandle, string $slug): ?Content
    {
        return $this->cache->remember(
            "content:{$setHandle}:{$slug}",
            fn (): ?Content => Content::query()
                ->whereHas('set', fn ($query) => $query->where('handle', $setHandle))
                ->where('slug', $slug)
                ->first(),
        );
    }

    public function findOrFail(string $setHandle, string $slug): Content
    {
        return $this->find($setHandle, $slug)
            ?? throw (new ModelNotFoundException)->setModel(Content::class, [$setHandle, $slug]);
    }

    public function byUuid(string $uuid): ?Content
    {
        return Content::query()->find($uuid);
    }
}
