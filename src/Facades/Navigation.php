<?php

declare(strict_types=1);

namespace LaraCeemes\Facades;

use Illuminate\Support\Facades\Facade;
use LaraCeemes\Managers\NavigationManager;

final class Navigation extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return NavigationManager::class;
    }
}
