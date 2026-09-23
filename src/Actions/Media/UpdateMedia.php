<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Media;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Media;
use LaraCeemes\Models\MediaFolder;
use RuntimeException;

final class UpdateMedia extends Action
{
    /** @param array<string, mixed> $data */
    public function execute(Media $media, array $data): Media
    {
        $validated = Validator::make($data, [
            'title' => ['nullable', 'string', 'max:255'],
            'alt' => ['nullable', 'string'],
            'caption' => ['nullable', 'string'],
            'folder_uuid' => ['nullable', 'uuid', Rule::exists('ceemes_media_folders', 'uuid')->where('disk', $media->disk)],
        ])->validate();

        return $this->transaction(function () use ($media, $validated): Media {
            if (array_key_exists('folder_uuid', $validated) && $validated['folder_uuid'] !== $media->folder_uuid) {
                $folder = $validated['folder_uuid'] ? MediaFolder::query()->whereKey($validated['folder_uuid'])->sole() : null;
                if ($folder !== null && $folder->disk !== $media->disk) {
                    throw ValidationException::withMessages(['folder_uuid' => 'Folder menggunakan storage disk yang berbeda.']);
                }
                $base = trim((string) config('ceemes.media.directory', 'ceemes'), '/\\');
                $directory = trim($base.($folder ? '/'.$folder->path : ''), '/\\');
                $newPath = trim($directory.'/'.$media->filename, '/');
                if ($newPath !== $media->path() && ! Storage::disk($media->disk)->move($media->path(), $newPath)) {
                    throw new RuntimeException('File tidak dapat dipindahkan ke folder tujuan.');
                }
                $validated['directory'] = $directory;
            }
            $media->update($validated);

            return $media->refresh();
        });
    }
}
