<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Media;

use Illuminate\Support\Facades\Storage;
use LaraCeemes\Actions\Action;
use LaraCeemes\Events\MediaDeleted;
use LaraCeemes\Exceptions\MediaInUse;
use LaraCeemes\Models\Media;
use LaraCeemes\Support\MediaUsageDetector;
use RuntimeException;

final class DeleteMedia extends Action
{
    public function __construct(private readonly MediaUsageDetector $usages) {}

    public function execute(Media $media): void
    {
        $usages = $this->usages->forUuid($media->uuid);

        if ($usages !== []) {
            throw new MediaInUse($usages);
        }

        $this->transaction(function () use ($media): void {
            $disk = Storage::disk($media->disk);

            if ($disk->exists($media->path()) && ! $disk->delete($media->path())) {
                throw new RuntimeException('The media file could not be deleted.');
            }

            $media->delete();
            event(new MediaDeleted($media));
        });
    }
}
