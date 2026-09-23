<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Media;

use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\MediaFolder;

final class DeleteMediaFolder extends Action
{
    public function execute(MediaFolder $folder): void
    {
        if ($folder->media()->exists() || $folder->children()->exists()) {
            throw ValidationException::withMessages([
                'folder' => 'Folder hanya dapat dihapus setelah seluruh file dan subfolder dipindahkan atau dihapus.',
            ]);
        }

        $this->transaction(fn () => $folder->delete());
    }
}
