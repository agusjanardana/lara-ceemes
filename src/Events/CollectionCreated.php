<?php

declare(strict_types=1);

namespace LaraCeemes\Events;

use LaraCeemes\Models\Collection;

final readonly class CollectionCreated
{
    public function __construct(public Collection $collection) {}
}
