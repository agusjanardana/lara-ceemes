<?php

declare(strict_types=1);

namespace LaraCeemes\Events;

use LaraCeemes\Models\Set;

final readonly class SetUpdated
{
    public function __construct(public Set $set) {}
}
