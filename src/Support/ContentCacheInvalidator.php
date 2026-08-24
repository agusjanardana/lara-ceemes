<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use LaraCeemes\Models\Entry;

final class ContentCacheInvalidator
{
    public function __construct(private readonly CeemesCache $cache) {}

    public function collection(string $handle): void
    {
        $this->cache->forget("collection:{$handle}");
        $this->cache->forget('collections');
    }

    public function entry(Entry $entry, ?string $previousSlug = null): void
    {
        $collectionHandle = $entry->collection()->value('handle');

        if (! is_string($collectionHandle)) {
            return;
        }

        $this->cache->forget("entry:{$collectionHandle}:{$entry->slug}");

        if ($previousSlug !== null && $previousSlug !== $entry->slug) {
            $this->cache->forget("entry:{$collectionHandle}:{$previousSlug}");
        }

        $this->collection($collectionHandle);
    }
}
