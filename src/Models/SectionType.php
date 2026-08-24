<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $uuid
 * @property string $name
 * @property string $handle
 * @property string|null $description
 * @property string|null $icon
 */
final class SectionType extends CeemesModel
{
    protected $table = 'ceemes_section_types';

    protected $fillable = [
        'name',
        'handle',
        'description',
        'icon',
    ];

    /** @return HasMany<SectionField, $this> */
    public function fields(): HasMany
    {
        return $this->hasMany(SectionField::class, 'section_type_uuid', 'uuid')
            ->orderBy('sort_order');
    }

    /** @return HasMany<Section, $this> */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'section_type_uuid', 'uuid');
    }
}
