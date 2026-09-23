<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Events\MediaUploaded;
use LaraCeemes\Models\Media;
use LaraCeemes\Models\MediaFolder;
use RuntimeException;
use Throwable;

final class UploadMedia extends Action
{
    /** @param array<string, mixed> $metadata */
    public function execute(UploadedFile $file, array $metadata = []): Media
    {
        $allowedMimes = config('ceemes.media.allowed_mimes', []);
        $maxSize = (int) config('ceemes.media.max_upload_size', 10240);
        $disk = (string) config('ceemes.media.disk', 'public');

        Validator::make(['file' => $file], [
            'file' => [
                'required',
                'file',
                "max:{$maxSize}",
                'mimetypes:'.implode(',', is_array($allowedMimes) ? $allowedMimes : []),
            ],
        ])->validate();

        $validatedMetadata = Validator::make($metadata, [
            'directory' => ['sometimes', 'string', 'max:255', 'not_regex:/\.\./'],
            'folder_uuid' => ['nullable', 'uuid', Rule::exists('ceemes_media_folders', 'uuid')->where('disk', $disk)],
            'title' => ['nullable', 'string', 'max:255'],
            'alt' => ['nullable', 'string'],
            'caption' => ['nullable', 'string'],
            'uploaded_by' => ['nullable'],
        ])->validate();

        $uuid = (string) Str::uuid();
        $extension = strtolower($file->getClientOriginalExtension() ?: (string) $file->guessExtension());
        $filename = $uuid.($extension !== '' ? ".{$extension}" : '');
        $folder = isset($validatedMetadata['folder_uuid'])
            ? MediaFolder::query()->whereKey($validatedMetadata['folder_uuid'])->where('disk', $disk)->firstOrFail()
            : null;
        $baseDirectory = trim((string) config('ceemes.media.directory', 'ceemes'), '/\\');
        $directory = trim($baseDirectory.($folder ? '/'.$folder->path : ''), '/\\');
        $path = ltrim($directory.'/'.$filename, '/');
        $stored = Storage::disk($disk)->putFileAs($directory, $file, $filename);

        if ($stored === false) {
            throw new RuntimeException('The media file could not be stored.');
        }

        try {
            return $this->transaction(function () use (
                $file,
                $validatedMetadata,
                $uuid,
                $extension,
                $filename,
                $disk,
                $directory,
            ): Media {
                [$width, $height] = $this->dimensions($file);
                $media = Media::query()->create([
                    'uuid' => $uuid,
                    'folder_uuid' => $validatedMetadata['folder_uuid'] ?? null,
                    'disk' => $disk,
                    'directory' => $directory,
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'extension' => $extension,
                    'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                    'size' => (int) $file->getSize(),
                    'width' => $width,
                    'height' => $height,
                    'title' => $validatedMetadata['title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'alt' => $validatedMetadata['alt'] ?? null,
                    'caption' => $validatedMetadata['caption'] ?? null,
                    'uploaded_by' => $validatedMetadata['uploaded_by'] ?? Auth::id(),
                ]);

                event(new MediaUploaded($media));

                return $media;
            });
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }
    }

    /** @return array{int|null, int|null} */
    private function dimensions(UploadedFile $file): array
    {
        $mime = $file->getMimeType();

        if (! is_string($mime) || ! str_starts_with($mime, 'image/') || $mime === 'image/svg+xml') {
            return [null, null];
        }

        $dimensions = getimagesize($file->getPathname());

        return is_array($dimensions)
            ? [(int) $dimensions[0], (int) $dimensions[1]]
            : [null, null];
    }
}
