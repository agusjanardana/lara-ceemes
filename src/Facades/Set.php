<?php

declare(strict_types=1);

namespace LaraCeemes\Facades;

use Illuminate\Support\Facades\Facade;
use LaraCeemes\Managers\SetManager;

final class Set extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SetManager::class;
    }
}
