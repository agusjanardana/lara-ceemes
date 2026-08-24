<?php

declare(strict_types=1);

namespace LaraCeemes\Facades;

use Illuminate\Support\Facades\Facade;
use LaraCeemes\Managers\MediaManager;

final class Media extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MediaManager::class;
    }
}
