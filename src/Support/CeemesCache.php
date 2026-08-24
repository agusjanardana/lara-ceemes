<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use Closure;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository;

final class CeemesCache
{
    private readonly Repository $store;

    public function __construct(CacheFactory $cache)
    {
        $configuredStore = config('ceemes.cache.store');
        $this->store = $cache->store(is_string($configuredStore) ? $configuredStore : null);
    }

    /**
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function remember(string $key, Closure $callback): mixed
    {
        if (! $this->enabled()) {
            return $callback();
        }

        return $this->store->remember(
            $this->key($key),
            (int) config('ceemes.cache.ttl', 3600),
            $callback,
        );
    }

    public function forget(string $key): bool
    {
        return $this->store->forget($this->key($key));
    }

    public function key(string $key): string
    {
        return trim((string) config('ceemes.cache.prefix', 'ceemes'), ':').':'.ltrim($key, ':');
    }

    public function enabled(): bool
    {
        return (bool) config('ceemes.cache.enabled', true);
    }
}
