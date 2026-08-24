<?php

declare(strict_types=1);

namespace LaraCeemes\Events;

use LaraCeemes\Models\Section;

final readonly class SectionUpdated
{
    public function __construct(public Section $section) {}
}
