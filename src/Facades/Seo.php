<?php

declare(strict_types=1);

namespace LaraCeemes\Facades;

use Illuminate\Support\Facades\Facade;
use LaraCeemes\Managers\SeoManager;

final class Seo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SeoManager::class;
    }
}
