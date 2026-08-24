<?php

declare(strict_types=1);

namespace LaraCeemes\Facades;

use Illuminate\Support\Facades\Facade;
use LaraCeemes\Managers\EntryManager;

final class Entry extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EntryManager::class;
    }
}
