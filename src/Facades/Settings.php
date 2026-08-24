<?php

declare(strict_types=1);

namespace LaraCeemes\Facades;

use Illuminate\Support\Facades\Facade;
use LaraCeemes\Managers\SettingManager;

final class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SettingManager::class;
    }
}
