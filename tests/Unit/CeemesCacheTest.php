<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Unit;

use LaraCeemes\Support\CeemesCache;
use LaraCeemes\Tests\TestCase;

final class CeemesCacheTest extends TestCase
{
    public function test_values_are_cached_with_the_ceemes_prefix(): void
    {
        $calls = 0;
        $cache = $this->app->make(CeemesCache::class);

        $first = $cache->remember('set:pages', function () use (&$calls): string {
            $calls++;

            return 'Pages';
        });

        $second = $cache->remember('set:pages', function () use (&$calls): string {
            $calls++;

            return 'Changed';
        });

        self::assertSame('Pages', $first);
        self::assertSame('Pages', $second);
        self::assertSame(1, $calls);
        self::assertSame('ceemes:set:pages', $cache->key('set:pages'));
    }

    public function test_disabled_cache_executes_the_callback_each_time(): void
    {
        config()->set('ceemes.cache.enabled', false);
        $calls = 0;
        $cache = $this->app->make(CeemesCache::class);

        $cache->remember('settings', function () use (&$calls): int {
            return ++$calls;
        });
        $value = $cache->remember('settings', function () use (&$calls): int {
            return ++$calls;
        });

        self::assertSame(2, $value);
    }
}
