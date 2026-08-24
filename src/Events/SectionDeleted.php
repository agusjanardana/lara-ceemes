<?php

declare(strict_types=1);

namespace LaraCeemes\Events;

use LaraCeemes\Models\Section;

final readonly class SectionDeleted
{
    public function __construct(public Section $section) {}
}
