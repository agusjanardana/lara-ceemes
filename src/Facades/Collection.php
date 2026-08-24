<?php

declare(strict_types=1);

namespace LaraCeemes\Facades;

use Illuminate\Support\Facades\Facade;
use LaraCeemes\Managers\CollectionManager;

final class Collection extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CollectionManager::class;
    }
}
