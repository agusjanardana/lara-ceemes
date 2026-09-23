<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Console\Command;
use LaraCeemes\Models\CategoryGroup;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Models\Set;
use LaraCeemes\Support\CeemesCache;

final class ClearCacheCommand extends Command
{
    protected $signature = 'ceemes:cache:clear';

    protected $description = 'Clear Lara Ceemes cache contents without flushing the application cache';

    public function handle(CeemesCache $cache): int
    {
        $cache->forget('settings');
        $cache->forget('sets');

        foreach (Set::query()->pluck('handle') as $handle) {
            $cache->forget("set:{$handle}");
        }

        foreach (CategoryGroup::query()->pluck('handle') as $handle) {
            $cache->forget("categoryGroup:{$handle}");
        }

        foreach (Navigation::withoutGlobalScope('ceemes_site')->get(['site_uuid', 'handle']) as $navigation) {
            $cache->forget("site:{$navigation->site_uuid}:navigation:{$navigation->handle}");
        }

        Content::withoutGlobalScope('ceemes_site')->with('set')->each(function (Content $content) use ($cache): void {
            $cache->forget("site:{$content->site_uuid}:content:{$content->set->handle}:{$content->slug}");
        });

        $this->components->info('Lara Ceemes cache cleared.');

        return self::SUCCESS;
    }
}
