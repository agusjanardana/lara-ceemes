<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $uuid
 * @property string $name
 * @property string $handle
 * @property string|null $description
 */
final class Taxonomy extends CeemesModel
{
    protected $table = 'ceemes_taxonomies';

    protected $fillable = ['name', 'handle', 'description'];

    /** @return HasMany<Term, $this> */
    public function terms(): HasMany
    {
        return $this->hasMany(Term::class, 'taxonomy_uuid', 'uuid')->orderBy('sort_order');
    }
}
