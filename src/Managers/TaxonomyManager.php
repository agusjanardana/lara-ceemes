<?php

declare(strict_types=1);

namespace LaraCeemes\Managers;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use LaraCeemes\Models\Taxonomy;
use LaraCeemes\Models\Term;

final class TaxonomyManager extends Manager
{
    public function get(string $handle): Taxonomy
    {
        return $this->cache->remember(
            "taxonomy:{$handle}",
            fn (): Taxonomy => Taxonomy::query()->where('handle', $handle)->first()
                ?? throw (new ModelNotFoundException)->setModel(Taxonomy::class, [$handle]),
        );
    }

    /** @return EloquentCollection<int, Term> */
    public function terms(string $handle): EloquentCollection
    {
        return $this->get($handle)->terms()->get();
    }

    public function term(string $taxonomyHandle, string $slug): ?Term
    {
        return $this->get($taxonomyHandle)->terms()->where('slug', $slug)->first();
    }
}
