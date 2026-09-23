<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use LaraCeemes\Models\Content;

final class ContentCacheInvalidator
{
    public function __construct(private readonly CeemesCache $cache) {}

    public function set(string $handle): void
    {
        $this->cache->forget("set:{$handle}");
        $this->cache->forget('sets');
    }

    public function content(Content $content, ?string $previousSlug = null): void
    {
        $setHandle = $content->set()->value('handle');

        if (! is_string($setHandle)) {
            return;
        }

        $siteKey = 'site:'.$content->site_uuid.':';
        $this->cache->forget("{$siteKey}content:{$setHandle}:{$content->slug}");

        if ($previousSlug !== null && $previousSlug !== $content->slug) {
            $this->cache->forget("{$siteKey}content:{$setHandle}:{$previousSlug}");
        }

        $this->set($setHandle);
    }
}
