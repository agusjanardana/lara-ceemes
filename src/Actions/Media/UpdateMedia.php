<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Media;

use Illuminate\Support\Facades\Validator;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Media;

final class UpdateMedia extends Action
{
    /** @param array<string, mixed> $data */
    public function execute(Media $media, array $data): Media
    {
        $validated = Validator::make($data, [
            'title' => ['nullable', 'string', 'max:255'],
            'alt' => ['nullable', 'string'],
            'caption' => ['nullable', 'string'],
        ])->validate();

        return $this->transaction(function () use ($media, $validated): Media {
            $media->update($validated);

            return $media->refresh();
        });
    }
}
