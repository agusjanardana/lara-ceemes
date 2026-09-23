<?php

declare(strict_types=1);

namespace LaraCeemes\Facades;

use Illuminate\Support\Facades\Facade;
use LaraCeemes\Managers\SiteManager;

final class Site extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SiteManager::class;
    }
}
