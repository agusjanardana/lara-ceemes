<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Taxonomies;

use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Taxonomy;
use LaraCeemes\Support\CeemesCache;

final class DeleteTaxonomy extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    public function execute(Taxonomy $taxonomy): void
    {
        if ($taxonomy->terms()->exists()) {
            throw ValidationException::withMessages([
                'taxonomy' => "Taxonomy [{$taxonomy->handle}] still contains Terms.",
            ]);
        }

        $this->transaction(function () use ($taxonomy): void {
            $handle = $taxonomy->handle;
            $taxonomy->delete();
            $this->cache->forget("taxonomy:{$handle}");
        });
    }
}
