<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Console\Command;
use LaraCeemes\Models\Collection;
use LaraCeemes\Models\Entry;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Models\Taxonomy;
use LaraCeemes\Support\CeemesCache;

final class ClearCacheCommand extends Command
{
    protected $signature = 'ceemes:cache:clear';

    protected $description = 'Clear Lara Ceemes cache entries without flushing the application cache';

    public function handle(CeemesCache $cache): int
    {
        $cache->forget('settings');
        $cache->forget('collections');

        foreach (Collection::query()->pluck('handle') as $handle) {
            $cache->forget("collection:{$handle}");
        }

        foreach (Taxonomy::query()->pluck('handle') as $handle) {
            $cache->forget("taxonomy:{$handle}");
        }

        foreach (Navigation::query()->pluck('handle') as $handle) {
            $cache->forget("navigation:{$handle}");
        }

        Entry::query()->with('collection')->each(function (Entry $entry) use ($cache): void {
            $cache->forget("entry:{$entry->collection->handle}:{$entry->slug}");
        });

        $this->components->info('Lara Ceemes cache cleared.');

        return self::SUCCESS;
    }
}
