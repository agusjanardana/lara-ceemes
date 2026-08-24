<?php

declare(strict_types=1);

namespace LaraCeemes\Facades;

use Illuminate\Support\Facades\Facade;
use LaraCeemes\Managers\TaxonomyManager;

final class Taxonomy extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TaxonomyManager::class;
    }
}
