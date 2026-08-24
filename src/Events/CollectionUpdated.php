<?php

declare(strict_types=1);

namespace LaraCeemes\Events;

use LaraCeemes\Models\Collection;

final readonly class CollectionUpdated
{
    public function __construct(public Collection $collection) {}
}
