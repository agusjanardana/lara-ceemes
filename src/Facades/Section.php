<?php

declare(strict_types=1);

namespace LaraCeemes\Facades;

use Illuminate\Support\Facades\Facade;
use LaraCeemes\Managers\SectionManager;

final class Section extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SectionManager::class;
    }
}
