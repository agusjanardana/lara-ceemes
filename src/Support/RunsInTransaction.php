<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use Closure;
use Illuminate\Support\Facades\DB;

trait RunsInTransaction
{
    /**
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    protected function transaction(Closure $callback): mixed
    {
        return DB::transaction($callback);
    }
}
