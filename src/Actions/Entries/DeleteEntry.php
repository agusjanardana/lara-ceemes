<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Entries;

use LaraCeemes\Actions\Action;
use LaraCeemes\Events\EntryDeleted;
use LaraCeemes\Models\Entry;
use LaraCeemes\Support\ContentCacheInvalidator;

final class DeleteEntry extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    public function execute(Entry $entry): void
    {
        $this->transaction(function () use ($entry): void {
            $entry->delete();
            $this->cache->entry($entry);
            event(new EntryDeleted($entry));
        });
    }
}
