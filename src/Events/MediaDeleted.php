<?php

declare(strict_types=1);

namespace LaraCeemes\Events;

use LaraCeemes\Models\Media;

final readonly class MediaDeleted
{
    public function __construct(public Media $media) {}
}
