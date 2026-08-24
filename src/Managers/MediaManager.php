<?php

declare(strict_types=1);

namespace LaraCeemes\Managers;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use LaraCeemes\Models\Media;
use LaraCeemes\Support\CeemesCache;
use LaraCeemes\Support\MediaUsage;
use LaraCeemes\Support\MediaUsageDetector;

final class MediaManager extends Manager
{
    public function __construct(
        CeemesCache $cache,
        private readonly MediaUsageDetector $usageDetector,
    ) {
        parent::__construct($cache);
    }

    public function find(string $uuid): ?Media
    {
        return Media::query()->find($uuid);
    }

    /** @return EloquentCollection<int, Media> */
    public function all(): EloquentCollection
    {
        return Media::query()->latest()->get();
    }

    /** @return EloquentCollection<int, Media> */
    public function search(string $query): EloquentCollection
    {
        return Media::query()
            ->where(function ($builder) use ($query): void {
                $builder->where('original_filename', 'like', "%{$query}%")
                    ->orWhere('title', 'like', "%{$query}%")
                    ->orWhere('alt', 'like', "%{$query}%");
            })
            ->latest()
            ->get();
    }

    /** @return array<int, MediaUsage> */
    public function usages(string|Media $media): array
    {
        return $this->usageDetector->forUuid($media instanceof Media ? $media->uuid : $media);
    }
}
