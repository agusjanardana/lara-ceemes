<?php

declare(strict_types=1);

namespace LaraCeemes\Events;

use LaraCeemes\Models\Entry;

final readonly class EntryDeleted
{
    public function __construct(public Entry $entry) {}
}
