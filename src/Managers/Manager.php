<?php

declare(strict_types=1);

namespace LaraCeemes\Managers;

use LaraCeemes\Support\CeemesCache;

abstract class Manager
{
    public function __construct(protected readonly CeemesCache $cache) {}
}
