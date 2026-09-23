<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Media\CreateMediaFolder;
use LaraCeemes\Actions\Media\DeleteMediaFolder;
use LaraCeemes\Models\MediaFolder;

final class MediaFolderController extends AdminController
{
    public function store(Request $request, CreateMediaFolder $action): RedirectResponse
    {
        $folder = $action->execute($request->only(['name', 'parent_uuid']));

        return redirect()->route('ceemes.admin.media.index', ['folder' => $folder->uuid])
            ->with('success', 'Folder dibuat.');
    }

    public function destroy(MediaFolder $mediaFolder, DeleteMediaFolder $action): RedirectResponse
    {
        $parent = $mediaFolder->parent_uuid;
        $action->execute($mediaFolder);

        return redirect()->route('ceemes.admin.media.index', array_filter(['folder' => $parent]))
            ->with('success', 'Folder dihapus.');
    }
}
