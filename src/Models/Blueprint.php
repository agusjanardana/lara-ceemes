<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $uuid
 * @property string $collection_uuid
 * @property string $name
 * @property string $handle
 * @property-read Collection $collection
 */
final class Blueprint extends CeemesModel
{
    protected $table = 'ceemes_blueprints';

    protected $fillable = [
        'collection_uuid',
        'name',
        'handle',
    ];

    /** @return BelongsTo<Collection, $this> */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class, 'collection_uuid', 'uuid');
    }

    /** @return HasMany<BlueprintField, $this> */
    public function fields(): HasMany
    {
        return $this->hasMany(BlueprintField::class, 'blueprint_uuid', 'uuid')
            ->orderBy('sort_order');
    }

    /** @return HasMany<Entry, $this> */
    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class, 'blueprint_uuid', 'uuid');
    }
}
