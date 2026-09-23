<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Media;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\MediaFolder;

final class CreateMediaFolder extends Action
{
    /** @param array<string, mixed> $data */
    public function execute(array $data): MediaFolder
    {
        $disk = (string) config('ceemes.media.disk', 'public');
        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:100'],
            'parent_uuid' => ['nullable', Rule::exists('ceemes_media_folders', 'uuid')->where('disk', $disk)],
        ])->validate();
        $parent = isset($validated['parent_uuid']) ? MediaFolder::query()->whereKey($validated['parent_uuid'])->sole() : null;
        $segment = Str::slug($validated['name']);
        $path = trim(($parent?->path ? $parent->path.'/' : '').$segment, '/');

        Validator::make(['path' => $path], [
            'path' => ['required', Rule::unique('ceemes_media_folders', 'path')->where('disk', $disk)],
        ], ['path.unique' => 'Folder dengan nama tersebut sudah ada di lokasi ini.'])->validate();

        return $this->transaction(fn (): MediaFolder => MediaFolder::query()->create([
            'parent_uuid' => $parent?->uuid,
            'disk' => $disk,
            'name' => $validated['name'],
            'path' => $path,
            'created_by' => Auth::id(),
        ]));
    }
}
