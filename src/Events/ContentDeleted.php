<?php

declare(strict_types=1);

namespace LaraCeemes\Events;

use LaraCeemes\Models\Content;

final readonly class ContentDeleted
{
    public function __construct(public Content $content) {}
}
