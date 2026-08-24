<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $uuid
 * @property string $taxonomy_uuid
 * @property string|null $parent_uuid
 * @property string $name
 * @property string $slug
 * @property array<string, mixed>|null $data
 * @property int $sort_order
 * @property-read Taxonomy $taxonomy
 */
final class Term extends CeemesModel
{
    protected $table = 'ceemes_terms';

    protected $fillable = [
        'taxonomy_uuid',
        'parent_uuid',
        'name',
        'slug',
        'data',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<Taxonomy, $this> */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class, 'taxonomy_uuid', 'uuid');
    }

    /** @return BelongsTo<Term, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_uuid', 'uuid');
    }

    /** @return HasMany<Term, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_uuid', 'uuid')->orderBy('sort_order');
    }

    /** @return BelongsToMany<Entry, $this> */
    public function entries(): BelongsToMany
    {
        return $this->belongsToMany(
            Entry::class,
            'ceemes_entry_term',
            'term_uuid',
            'entry_uuid',
            'uuid',
            'uuid',
        )->withPivot('field_handle');
    }
}
