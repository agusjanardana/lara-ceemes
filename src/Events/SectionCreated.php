<?php

declare(strict_types=1);

namespace LaraCeemes\Events;

use LaraCeemes\Models\Section;

final readonly class SectionCreated
{
    public function __construct(public Section $section) {}
}
