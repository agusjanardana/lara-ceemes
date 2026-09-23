<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use Illuminate\Support\Facades\Schema;
use LaraCeemes\Models\Site;
use RuntimeException;

final class SiteContext
{
    private ?Site $site = null;

    public function enabled(): bool
    {
        return (bool) config('ceemes.multisite.enabled', false);
    }

    public function current(): Site
    {
        if (! Schema::hasTable('ceemes_sites')) {
            throw new RuntimeException('Lara Ceemes Sites are not migrated yet. Run php artisan migrate.');
        }

        $routeHandle = request()->route('ceemesSite');
        if ($this->enabled() && is_string($routeHandle) && $routeHandle !== '') {
            if ($this->site?->handle === $routeHandle && $this->site->is_enabled) {
                return $this->site;
            }

            return $this->useHandle($routeHandle, enabledOnly: true);
        }

        if ($this->site !== null) {
            return $this->site;
        }

        if ($this->enabled() && request()->hasSession()) {
            $sessionUuid = request()->session()->get('ceemes_site_uuid');
            if (is_string($sessionUuid)) {
                $sessionSite = Site::query()->whereKey($sessionUuid)->where('is_enabled', true)->first();
                if ($sessionSite !== null) {
                    return $this->use($sessionSite);
                }
            }
        }

        $default = Site::query()->where('is_default', true)->where('is_enabled', true)->first()
            ?? Site::query()->where('is_enabled', true)->orderBy('sort_order')->first();

        if ($default === null) {
            throw new RuntimeException('Lara Ceemes requires at least one enabled Site.');
        }

        return $this->use($default);
    }

    public function uuid(): ?string
    {
        if (! Schema::hasTable('ceemes_sites')) {
            return null;
        }

        return $this->current()->uuid;
    }

    public function use(Site $site, bool $persist = false): Site
    {
        $this->site = $site;

        if ($persist && request()->hasSession()) {
            request()->session()->put('ceemes_site_uuid', $site->uuid);
        }

        app()->setLocale($site->locale);

        return $site;
    }

    public function useHandle(string $handle, bool $enabledOnly = false): Site
    {
        $site = Site::query()
            ->where('handle', $handle)
            ->when($enabledOnly, fn ($query) => $query->where('is_enabled', true))
            ->firstOrFail();

        return $this->use($site);
    }

    public function cacheKey(string $key): string
    {
        return 'site:'.($this->uuid() ?? 'none').':'.ltrim($key, ':');
    }

    public function forget(): void
    {
        $this->site = null;
    }
}
