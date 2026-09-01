<?php

declare(strict_types=1);

namespace LaraCeemes\Facades;

use Illuminate\Support\Facades\Facade;
use LaraCeemes\Managers\ContentManager;

final class Content extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ContentManager::class;
    }
}
