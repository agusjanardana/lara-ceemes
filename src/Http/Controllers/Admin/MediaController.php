<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Media\DeleteMedia;
use LaraCeemes\Actions\Media\UpdateMedia;
use LaraCeemes\Actions\Media\UploadMedia;
use LaraCeemes\Exceptions\MediaInUse;
use LaraCeemes\Managers\MediaManager;
use LaraCeemes\Models\Media;
use LaraCeemes\Models\MediaFolder;

final class MediaController extends AdminController
{
    public function index(Request $request, MediaManager $media): View
    {
        $query = $request->string('q')->trim()->toString();
        $type = $request->string('type')->toString();
        $folder = $request->string('folder')->toString();
        $disk = (string) config('ceemes.media.disk', 'public');
        $folders = MediaFolder::query()->where('disk', $disk)->orderBy('path')->get();

        return $this->render('ceemes::admin.media', [
            'mediaItems' => $media->filter($query, $type, $folder),
            'folders' => $folders,
            'activeFolder' => $folders->firstWhere('uuid', $folder),
            'mediaDisk' => $disk,
        ]);
    }

    public function store(Request $request, UploadMedia $action): RedirectResponse
    {
        $file = $request->file('file');

        if ($file === null) {
            return back()->withErrors(['file' => 'Please choose a file.']);
        }

        $action->execute($file, $request->only(['title', 'alt', 'caption', 'folder_uuid']));

        return $this->success('ceemes.admin.media.index', 'Media uploaded.');
    }

    public function update(Request $request, Media $media, UpdateMedia $action): RedirectResponse
    {
        $action->execute($media, $request->only(['title', 'alt', 'caption', 'folder_uuid']));

        return $this->success('ceemes.admin.media.index', 'Media updated.');
    }

    public function destroy(Media $media, DeleteMedia $action): RedirectResponse
    {
        try {
            $action->execute($media);
        } catch (MediaInUse $exception) {
            return back()
                ->withErrors(['media' => $exception->getMessage()])
                ->with('media_usages', array_map(fn ($usage): array => $usage->toArray(), $exception->usages));
        }

        return $this->success('ceemes.admin.media.index', 'Media deleted.');
    }
}
