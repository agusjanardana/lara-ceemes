<?php

declare(strict_types=1);

namespace LaraCeemes\Managers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use LaraCeemes\Models\Navigation;

final class NavigationManager extends Manager
{
    public function get(string $handle): Navigation
    {
        return $this->cache->remember(
            "navigation:{$handle}",
            fn (): Navigation => Navigation::query()->where('handle', $handle)->first()
                ?? throw (new ModelNotFoundException)->setModel(Navigation::class, [$handle]),
        );
    }
}
