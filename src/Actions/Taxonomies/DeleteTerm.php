<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Taxonomies;

use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Term;
use LaraCeemes\Support\CeemesCache;

final class DeleteTerm extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    public function execute(Term $term): void
    {
        $this->transaction(function () use ($term): void {
            $handle = $term->taxonomy->handle;
            $term->delete();
            $this->cache->forget("taxonomy:{$handle}");
        });
    }
}
