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

    /** @return EloquentCollection<int, Media> */
    public function filter(string $search = '', string $type = '', string $folder = ''): EloquentCollection
    {
        return Media::query()
            ->with('folder')
            ->when($search !== '', fn ($builder) => $builder->where(function ($builder) use ($search): void {
                $builder->where('original_filename', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('alt', 'like', "%{$search}%");
            }))
            ->when($type === 'image', fn ($builder) => $builder->where('mime_type', 'like', 'image/%'))
            ->when($type === 'document', fn ($builder) => $builder->where(function ($builder): void {
                $builder->where('mime_type', 'like', 'application/%')->orWhere('mime_type', 'like', 'text/%');
            }))
            ->when($type === 'other', fn ($builder) => $builder
                ->where('mime_type', 'not like', 'image/%')
                ->where('mime_type', 'not like', 'application/%')
                ->where('mime_type', 'not like', 'text/%'))
            ->when($folder === 'root', fn ($builder) => $builder->whereNull('folder_uuid'))
            ->when($folder !== '' && $folder !== 'root', fn ($builder) => $builder->where('folder_uuid', $folder))
            ->latest()
            ->get();
    }

    /** @return array<int, MediaUsage> */
    public function usages(string|Media $media): array
    {
        return $this->usageDetector->forUuid($media instanceof Media ? $media->uuid : $media);
    }
}
