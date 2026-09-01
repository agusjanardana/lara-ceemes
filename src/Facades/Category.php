<?php

declare(strict_types=1);

namespace LaraCeemes\Facades;

use Illuminate\Support\Facades\Facade;
use LaraCeemes\Managers\CategoryManager;

final class Category extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CategoryManager::class;
    }
}
