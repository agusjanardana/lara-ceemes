<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Contents;

use LaraCeemes\Actions\Action;
use LaraCeemes\Events\ContentDeleted;
use LaraCeemes\Models\Content;
use LaraCeemes\Support\ContentCacheInvalidator;

final class DeleteContent extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    public function execute(Content $content): void
    {
        $this->transaction(function () use ($content): void {
            $content->delete();
            $this->cache->content($content);
            event(new ContentDeleted($content));
        });
    }
}
